<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsWebsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'website',
    ];

    protected $table = 'news_websites';

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
