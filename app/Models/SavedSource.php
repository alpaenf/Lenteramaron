<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'research_topic_id',
        'source_type',
        'book_id',
        'external_source_id',
        'notes',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function researchTopic()
    {
        return $this->belongsTo(ResearchTopic::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function externalSource()
    {
        return $this->belongsTo(ExternalSource::class);
    }
}
