<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Book::create([
            'title' => 'Belajar Laravel 11',
            'author' => 'Taylor Otwell',
            'publisher' => 'Laravel Press',
            'year' => 2024,
            'category' => 'Technology',
            'type' => 'physical',
            'stock' => 5,
            'rack_location' => 'Rak A1',
        ]);

        \App\Models\Book::create([
            'title' => 'React for Beginners',
            'author' => 'Dan Abramov',
            'publisher' => 'React Press',
            'year' => 2023,
            'category' => 'Technology',
            'type' => 'ebook',
            'file_preview' => 'previews/react-preview.pdf',
            'file_full' => 'ebooks/react-full.pdf',
        ]);
    }
}
