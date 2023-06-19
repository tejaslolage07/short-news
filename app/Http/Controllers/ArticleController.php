<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Find the article by its id.
     */
    public function getArticle($id)
    {
        return Article::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Article $article, array $data)
    {
        //update the article's data
        //only those fields would be set, that are mass-assignable.
        $article->update($data);

        // Save the updated record
        $article->save();
    }
}
