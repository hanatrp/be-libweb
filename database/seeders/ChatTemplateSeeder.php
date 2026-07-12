<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ChatTemplate::create([
            'keyword' => 'jam buka',
            'answer' => 'Perpustakaan buka dari Senin - Jumat (08:00 - 16:00 WIB).',
        ]);

        \App\Models\ChatTemplate::create([
            'keyword' => 'denda',
            'answer' => 'Denda keterlambatan adalah Rp2.000 per hari.',
        ]);
    }
}
