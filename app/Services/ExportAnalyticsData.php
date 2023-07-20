<?php 

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportAnalyticsData
{
    public function exportCsv()
    {
        $fileName = 'exported_data.csv';
        $articles = DB::table('articles')->get();

        $csvExporter = \League\Csv\Writer::createFromPath(storage_path($fileName), 'w+');
        $csvExporter->insertOne(['article_id', 'original_article_chars', 'summarized_article_chars']);

        foreach ($articles as $row) {
            $s3Filename = $row->article_s3_filename;
            $originalArticle = Storage::disk('s3')->get($s3Filename);
            $summarizedArticle = $row->short_news;
            $originalArticleChars = strlen($originalArticle);
            $summarizedArticleChars = strlen($summarizedArticle);
            $csvExporter->insertOne([$row->id, $originalArticleChars, $summarizedArticleChars]);
        }

        return Response::download(storage_path($fileName))->deleteFileAfterSend(true);
    }
}
