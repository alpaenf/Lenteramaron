<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'source_provider',
        'title',
        'authors',
        'publication_year',
        'publisher_or_journal',
        'doi',
        'url',
        'pdf_url',
        'abstract',
        'citation_count',
        'open_access',
        'keywords',
    ];

    protected $casts = [
        'authors' => 'array',
        'keywords' => 'array',
        'open_access' => 'boolean',
        'publication_year' => 'integer',
        'citation_count' => 'integer',
    ];

    public function savedSources()
    {
        return $this->hasMany(SavedSource::class);
    }
}
