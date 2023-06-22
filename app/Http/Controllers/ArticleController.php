<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    public function getArticle($id)
    {
        return Article::findOrFail($id);
    }

    public function update(Article $article, array $data): Article
    {
        // only those fields would be set, that are mass-assignable.
        $article->update($data);

        $article->save();

        return $article;
    }
}
