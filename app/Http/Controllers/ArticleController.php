<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Find the article by its id.
     *
     * @param mixed $id
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
        // update the article's data
        // only those fields would be set, that are mass-assignable.
        $article->update($data);

        // Save the updated record
        $article->save();
    }

    /**
     * Returns count of articles, the next page url and prev page url.
     * Laravel handles the cursor pagination automatically. We just need to call the cursorPaginate($count) method.
     * @param count: number of articles to be returned per page
     * @return \Illuminate\Http\JsonResponse : if success
     * @return 500 response code             : if any error occurs
     */
    public function index(Request $request)
    {
        try {
            // Return those articles that have short_news not empty, ordered by published_at, with website name, and paginate them cursor based.
            $DBresponse = DB::table('articles')->where('short_news', '!=', '')
                ->orderBy('published_at', 'desc')->join('news_websites', 'news_websites.id', '=', 'articles.news_website_id')->select('articles.*', 'news_websites.website')
                ->cursorPaginate($request->input('count', 100))->toArray()
            ;
            $data = [
                'articles' => $DBresponse['data'],
                'next_page_url' => $DBresponse['next_page_url'],
                'prev_page_url' => $DBresponse['prev_page_url'],
                'per_page' => $DBresponse['per_page'],
            ];

            return response()->json($data);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Internal Server Error!'], 500);
        }
    }
}
