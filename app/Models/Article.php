<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'short_news',
        'article_s3_filename',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    /**
     * Get the news website that owns the article.
     */
    public function newsWebsite()
    {
        return $this->belongsTo(NewsWebsite::class);
    }
}
