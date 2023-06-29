<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        return Article::with('newsWebsite')
            // ->select('id', 'short_news', 'headline', 'author', 'article_url', 'image_url', 'published_at', 'news_website_id')
            ->where('short_news', '!=', '', 'and')
            ->where('news_website_id', '!=', null)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->cursorPaginate(perPage: $request->count ?? 100)
        ;
    }
}
