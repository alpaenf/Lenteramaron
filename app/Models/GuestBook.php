<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_no',
        'name',
        'institution',
        'purpose',
        'feedback',
        'note',
        'date',
        'time',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
