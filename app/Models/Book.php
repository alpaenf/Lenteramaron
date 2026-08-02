<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_code',
        'isbn',
        'title',
        'author',
        'publisher',
        'year',
        'category_id',
        'shelf',
        'stock',
        'cover',
        'description',
    ];

    protected $casts = [
        'year' => 'integer',
        'stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'category_id');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class, 'book_id');
    }
}
