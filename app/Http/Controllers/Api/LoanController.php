<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use Carbon\Carbon;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            $loans = Loan::with(['user', 'book'])->get();
        } else {
            $loans = Loan::with('book')->where('user_id', $user->id)->get();
        }

        return response()->json([
            'loans' => $loans
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'nullable|exists:users,id'
        ]);

        $userId = $request->user()->id;
        if ($request->user()->role === 'admin' && $request->filled('student_id')) {
            $userId = $request->student_id;
        }

        // Check if book has stock
        $book = \App\Models\Book::findOrFail($request->book_id);
        if ($book->type === 'physical' && $book->stock <= 0) {
            return response()->json(['error' => 'Stok buku habis'], 400);
        }

        $loan = Loan::create([
            'user_id' => $userId,
            'book_id' => $request->book_id,
            'status' => 'borrowed',
            'borrow_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays((int) cache('max_loan_days', 7))
        ]);

        return response()->json(['message' => 'Peminjaman berhasil dicatat', 'loan' => $loan]);
    }

    public function approve(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        if ($loan->status !== 'pending') {
            return response()->json(['error' => 'Status tidak valid'], 400);
        }

        $loan->update([
            'status' => 'borrowed',
            'borrow_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays((int) cache('max_loan_days', 7))
        ]);

        return response()->json(['message' => 'Pinjaman disetujui', 'loan' => $loan]);
    }

    public function returnBook(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        if (!in_array($loan->status, ['borrowed', 'overdue'])) {
            return response()->json(['error' => 'Buku belum dipinjam atau sudah dikembalikan'], 400);
        }

        $fine = 0;
        $today = Carbon::today();
        if ($today->gt($loan->due_date)) {
            $daysLate = $today->diffInDays($loan->due_date);
            $fine = $daysLate * (int) cache('fine_rate', 2000);
        }

        $loan->update([
            'status' => 'returned',
            'return_date' => $today,
            'fine_amount' => $fine
        ]);

        return response()->json(['message' => 'Buku dikembalikan', 'loan' => $loan]);
    }
}
