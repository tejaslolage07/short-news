<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsWebsite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website',
    ];

    protected $table = 'news_websites';

    /**
     * Get the articles for the news website.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
