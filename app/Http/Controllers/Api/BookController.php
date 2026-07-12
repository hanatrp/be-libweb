<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();
        
        if ($request->query('include_deleted') === 'true' && $request->user() && $request->user()->role === 'admin') {
            $query->withTrashed();
        }

        $books = $query->get();

        return response()->json([
            'books' => $books
        ]);
    }

    public function show($id)
    {
        $book = Book::findOrFail($id);
        return response()->json(['book' => $book]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'type' => $request->input('is_ebook') ? 'ebook' : 'physical',
            'stock' => $request->input('total_stock', 0),
            'cover_image' => $request->input('cover_url')
        ]);

        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'type' => 'required|in:physical,ebook',
            'stock' => 'nullable|integer',
            'rack_location' => 'nullable|string',
            'file_preview' => 'nullable|string',
            'file_full' => 'nullable|string',
        ]);

        $book = Book::create($request->all());

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'book' => $book
        ]);
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        
        $request->merge([
            'type' => $request->input('is_ebook') ? 'ebook' : 'physical',
            'stock' => $request->input('total_stock', $book->stock),
            'cover_image' => $request->input('cover_url')
        ]);

        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'type' => 'required|in:physical,ebook',
            'stock' => 'nullable|integer',
            'rack_location' => 'nullable|string',
        ]);

        $book->update($request->all());

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'book' => $book
        ]);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus secara lunak (SoftDelete)'
        ]);
    }

    public function restore($id)
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->restore();

        return response()->json([
            'message' => 'Buku berhasil dipulihkan dari daftar SoftDelete',
            'book' => $book
        ]);
    }
}
