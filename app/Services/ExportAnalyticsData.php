<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ExportAnalyticsData
{
    public const DIR = '';

    public function exportCsv(): void
    {
        $fileName = self::DIR.'analytics.csv';
        $articles = DB::table('articles')->get();

        $csvExporter = \League\Csv\Writer::createFromPath(storage_path($fileName), 'w+');
        $csvExporter->insertOne(['article_id', 'original_article_chars', 'summarized_article_chars']);

        foreach ($articles as $row) {
            $originalArticleLength = $this->getOriginalArticleLength($row->article_s3_filename);
            $summarizedArticleLength = $this->getSummarizedArticleLength($row->short_news);

            $csvExporter->insertOne([$row->id, $originalArticleLength, $summarizedArticleLength]);
        }

        Response::download(storage_path($fileName))->deleteFileAfterSend(true);
    }

    private function getSummarizedArticleLength(string $summarizedArticle): int
    {
        return strlen($summarizedArticle);
    }

    private function getOriginalArticleLength(string $s3Filename): int
    {
        $originalArticle = Storage::disk('s3')->get($s3Filename);

        return strlen($originalArticle);
    }
}
