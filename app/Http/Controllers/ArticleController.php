<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function getArticle($id)
    {
        return Article::findOrFail($id);
    }

    public function update(Article $article, array $data)
    {
        // only those fields would be set, that are mass-assignable.
        $article->update($data);

        $article->save();
    }

    public function index(Request $request)
    {
        // Return those articles that have short_news not empty, ordered by published_at, with website name, and paginate them cursor based.
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
