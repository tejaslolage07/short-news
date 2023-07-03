<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_news',
        'article_s3_filename',
        'country',
        'language',
        'category',
        'keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'country' => 'array',
        'category' => 'array',
        'keywords' => 'array',
    ];

    public function newsWebsite()
    {
        return $this->belongsTo(NewsWebsite::class);
    }
}
