<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnBook extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'return_no',
        'borrowing_id',
        'member_id',
        'book_id',
        'return_date',
        'condition',
        'late_days',
        'note',
    ];

    protected $casts = [
        'return_date' => 'date',
        'late_days' => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class, 'borrowing_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
