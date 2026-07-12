<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatTemplate;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $history = ChatMessage::where('sender_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->sender_id,
                    'role' => $msg->is_from_bot ? 'assistant' : 'user',
                    'message' => $msg->message,
                    'created_at' => $msg->created_at->toISOString()
                ];
            });

        return response()->json([
            'history' => $history
        ]);
    }

    public function destroy(Request $request)
    {
        ChatMessage::where('sender_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Riwayat percakapan berhasil dibersihkan',
            'history' => []
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $user = $request->user();
        
        // 1. Save User Message
        $userMsg = ChatMessage::create([
            'sender_id' => $user->id,
            'message' => $request->message,
            'is_from_bot' => false
        ]);

        $assistantResponse = '';
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey && $apiKey !== 'MY_GEMINI_API_KEY' && trim($apiKey) !== '') {
            try {
                // Grounding info
                $activeLoans = Loan::where('user_id', $user->id)
                    ->where('status', 'borrowed')
                    ->get();
                $loansStr = $activeLoans->count() > 0 ? $activeLoans->map(function ($l) {
                    return "Buku \"" . ($l->book ? $l->book->title : 'Buku') . "\" (jatuh tempo " . $l->due_date->toDateString() . ")";
                })->join(', ') : 'Tidak ada buku aktif yang dipinjam.';

                $catalog = Book::limit(10)->get()->map(function ($b) {
                    return "- {$b->title} ({$b->category}) - karya {$b->author}, sisa stok {$b->stock}";
                })->join("\n");

                $systemPrompt = "Anda adalah LibBot, virtual assistant pintar untuk LibWeb (Sistem Informasi Perpustakaan).\n"
                    . "Membantu siswa bernama {$user->name} (Email: {$user->email}).\n"
                    . "Berikut adalah data real-time perpustakaan kami saat ini:\n"
                    . "- Katalog Buku Tersedia:\n{$catalog}\n"
                    . "- Pengaturan: Maksimal pinjam 7 hari, tarif denda Rp2.000 per hari keterlambatan.\n"
                    . "- Buku yang sedang dipinjam siswa saat ini: {$loansStr}\n\n"
                    . "Berikan respons yang hangat, membantu, padat, dan sepenuhnya dalam Bahasa Indonesia. Jawab pertanyaan siswa berdasarkan info perpustakaan di atas.";

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'aistudio-build'
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $request->message]
                            ]
                        ]
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $resJson = $response->json();
                    $assistantResponse = $resJson['candidates'][0]['content']['parts'][0]['text'] ?? '';
                } else {
                    Log::error("Gemini API error: " . $response->body());
                    $assistantResponse = $this->fallbackBotAnswer($request->message, $user);
                }
            } catch (\Exception $e) {
                Log::error("Gemini connection error: " . $e->getMessage());
                $assistantResponse = $this->fallbackBotAnswer($request->message, $user);
            }
        } else {
            $assistantResponse = $this->fallbackBotAnswer($request->message, $user);
        }

        // 2. Save Assistant Response
        $botMsg = ChatMessage::create([
            'sender_id' => $user->id,
            'message' => $assistantResponse,
            'is_from_bot' => true
        ]);

        // Get updated history
        return $this->index($request);
    }

    private function fallbackBotAnswer($message, $user)
    {
        $msg = strtolower($message);
        
        if (str_contains($msg, 'denda') || str_contains($msg, 'tarif') || str_contains($msg, 'bayar')) {
            return "Halo {$user->name}, tarif denda keterlambatan perpustakaan saat ini adalah Rp2.000 per hari jika buku dikembalikan melewati tanggal jatuh tempo.";
        }

        if (str_contains($msg, 'pinjam') || str_contains($msg, 'sirkulasi') || str_contains($msg, 'cara')) {
            return "Untuk meminjam buku di LibWeb, cari buku pilihan Anda di katalog, lalu klik tombol \"Pinjam\". Durasi maksimal peminjaman adalah 7 hari.";
        }

        if (str_contains($msg, 'rekomendasi') || str_contains($msg, 'buku') || str_contains($msg, 'bagus')) {
            $books = Book::limit(3)->get();
            $titles = $books->map(function ($b) {
                return "\"{$b->title}\" oleh {$b->author} ({$b->category})";
            })->join(', ');
            return "Tentu! Berikut beberapa buku populer yang bisa Anda baca saat ini: {$titles}.";
        }

        return "Halo {$user->name}! Saya LibBot. Saya bisa membantu Anda memberikan info seputar: denda keterlambatan, sirkulasi pinjam, dan rekomendasi buku!";
    }
}
