<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    protected $fillable = [
        'short_news',
        'article_s3_filename',
        'country',
        'language',
        'category',
        'keywords',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'keywords' => 'array',
    ];

    public function newsWebsite()
    {
        return $this->belongsTo(NewsWebsite::class);
    }
}
