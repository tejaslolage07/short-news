<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalysisDataExporter
{
    private const DIR = 'storage/app/';
    private S3StorageService $s3StorageService;

    public function __construct(S3StorageService $s3StorageService)
    {
        $this->s3StorageService = $s3StorageService;
    }

    public function exportCsv(): void
    {
        $filePath = self::DIR.'analysis.csv';
        $articles = DB::table('articles')->get();

        $file = fopen($filePath, 'w');
        fputcsv($file, ['article_id', 'original_article_length', 'summarized_article_length']);

        foreach ($articles as $row) {
            $summarizedArticleLength = $this->getSummarizedArticleLength($row->short_news);
            $originalArticleJson = $this->s3StorageService->readFromS3Bucket($row->article_s3_filename);
            $originalArticle = json_decode($originalArticleJson, true);
            if(!$originalArticle){
                $this->writeRowToCsvFile($file, [$row->id, '', $summarizedArticleLength]);
                continue;
            }
            $originalArticleLength = $this->getOriginalArticleLength($originalArticle['content']);
            $this->writeRowToCsvFile($file, [$row->id, $originalArticleLength, $summarizedArticleLength]);
        }
        fclose($file);
    }
    private function getSummarizedArticleLength(string $summarizedArticle): int
    {
        return strlen($summarizedArticle);
    }

    private function writeRowToCsvFile($file, array $row): void
    {
        fputcsv($file, $row);
    }

    private function getOriginalArticleLength(string $articleContent): int
    {
        return strlen($articleContent);
    }
}
