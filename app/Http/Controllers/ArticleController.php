<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        return Article::with('newsWebsite')
            ->whereNotNull('short_news')
            ->whereNotNull('news_website_id')
            ->orderByDesc('published_at')
            ->orderBy('id')
            ->cursorPaginate(perPage: $request->count ?? 100)
        ;
    }
}
