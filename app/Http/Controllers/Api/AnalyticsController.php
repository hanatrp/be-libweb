<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // 1. Summary
        $totalBooks = Book::where('type', 'ebook')->count() + Book::where('type', 'physical')->count();
        $totalPhysicalStock = Book::where('type', 'physical')->sum('stock');
        $totalEbooks = Book::where('type', 'ebook')->count();
        $totalMembers = User::where('role', 'member')->count();
        
        $totalBorrowedCount = Loan::where('status', 'borrowed')->count();
        
        // Calculate overdue count (today is past due_date and status is borrowed)
        $overdueCount = Loan::where('status', 'borrowed')
            ->where('due_date', '<', Carbon::today())
            ->count();
            
        $totalFinesCollected = Loan::where('status', 'returned')->sum('fine_amount');
        
        // Calculate pending fines dynamically
        $pendingLoans = Loan::where('status', 'borrowed')
            ->where('due_date', '<', Carbon::today())
            ->get();
            
        $totalPendingFines = 0;
        foreach ($pendingLoans as $loan) {
            $daysLate = Carbon::today()->diffInDays($loan->due_date);
            $totalPendingFines += $daysLate * 2000;
        }

        // 2. Category Distribution
        $categories = Book::select('category', DB::raw('count(*) as value'))
            ->groupBy('category')
            ->get();

        // 3. Popular Books
        $popularBooks = Loan::select('book_id', DB::raw('count(*) as count'))
            ->groupBy('book_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $book = Book::find($item->book_id);
                return [
                    'title' => $book ? $book->title : 'Buku Terhapus',
                    'author' => $book ? $book->author : '',
                    'count' => $item->count
                ];
            });

        // 4. Circulation Trend (last 7 days)
        $circulationTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->toDateString();
            
            $borrowed = Loan::whereDate('borrow_date', $dateString)->count();
            $returned = Loan::whereDate('return_date', $dateString)->count();
            
            $circulationTrend[] = [
                'date' => $dateString,
                'borrowed' => $borrowed,
                'returned' => $returned
            ];
        }

        return response()->json([
            'summary' => [
                'totalBooks' => $totalBooks,
                'totalPhysicalStock' => (int) $totalPhysicalStock,
                'totalEbooks' => $totalEbooks,
                'totalMembers' => $totalMembers,
                'totalBorrowedCount' => $totalBorrowedCount,
                'overdueCount' => $overdueCount,
                'totalFinesCollected' => (int) $totalFinesCollected,
                'totalPendingFines' => $totalPendingFines,
            ],
            'categoryDistribution' => $categories,
            'popularBooks' => $popularBooks,
            'circulationTrend' => $circulationTrend
        ]);
    }
}
