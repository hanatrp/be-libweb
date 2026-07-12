<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Loan extends Model
{
    protected $fillable = [
        'user_id', 'book_id', 'borrow_date', 'due_date', 'return_date',
        'status', 'fine_amount'
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    protected $with = ['book', 'user'];

    protected $appends = ['book_title', 'book_author', 'book_cover', 'student_name'];

    protected function bookTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->book?->title,
        );
    }

    protected function bookAuthor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->book?->author,
        );
    }

    protected function bookCover(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->book?->cover_image,
        );
    }

    protected function studentName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user?->name,
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === 'borrowed' && $this->due_date && Carbon::today()->gt(Carbon::parse($this->due_date))) {
                    return 'overdue';
                }
                return $value;
            }
        );
    }

    protected function fineAmount(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->status === 'overdue' && $this->due_date) {
                    $daysLate = Carbon::today()->diffInDays(Carbon::parse($this->due_date));
                    return $daysLate * (int) cache('fine_rate', 2000);
                }
                return (int) $value;
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    protected static function booted(): void
    {
        static::created(function (Loan $loan) {
            if ($loan->status === 'borrowed') {
                if ($loan->book && $loan->book->type === 'physical' && $loan->book->stock > 0) {
                    $loan->book->decrement('stock');
                }
            }
        });

        static::updated(function (Loan $loan) {
            // Update stock when borrowed
            if ($loan->isDirty('status') && $loan->status === 'borrowed') {
                if ($loan->book && $loan->book->type === 'physical' && $loan->book->stock > 0) {
                    $loan->book->decrement('stock');
                }
            }

            // Update stock when returned
            if ($loan->isDirty('status') && $loan->status === 'returned') {
                if ($loan->book && $loan->book->type === 'physical') {
                    $loan->book->increment('stock');
                }
            }
        });
    }
}
