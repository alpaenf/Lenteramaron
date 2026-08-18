<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query_text',
        'normalized_query',
        'filters',
        'results_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
