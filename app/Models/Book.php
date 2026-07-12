<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'author', 'publisher', 'year', 'category',
        'cover_image', 'rack_location', 'type', 'stock',
        'file_preview', 'file_full'
    ];

    protected $appends = ['is_available', 'is_ebook', 'cover_url', 'total_stock'];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === 'physical' && $this->stock > 0,
        );
    }

    protected function isEbook(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === 'ebook',
        );
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cover_image,
        );
    }

    protected function totalStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock,
        );
    }
}
