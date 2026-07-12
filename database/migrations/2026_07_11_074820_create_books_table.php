<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('year')->nullable();
            $table->string('category')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('rack_location')->nullable(); // For physical books
            $table->enum('type', ['physical', 'ebook'])->default('physical');
            $table->integer('stock')->default(0); // For physical books
            $table->string('file_preview')->nullable(); // For ebooks
            $table->string('file_full')->nullable(); // For ebooks
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
