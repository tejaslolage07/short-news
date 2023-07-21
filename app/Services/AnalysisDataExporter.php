<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AnalysisDataExporter
{
    private S3StorageService $s3StorageService;

    public function __construct(S3StorageService $s3StorageService)
    {
        $this->s3StorageService = $s3StorageService;
    }

    public function exportCsv(): void
    {
        $articles = DB::table('articles')->get();

        $file = fopen(storage_path('analysis.csv'), 'w');

        fputcsv($file, ['article_id', 'original_article_length', 'summarized_article_length']);

        foreach ($articles as $row) {
            $summarizedArticleLength = $this->getArticleLength($row->short_news);
            $originalArticleLength = $this->getOriginalArticleLength($row->article_s3_filename);
            $row = [$row->id, $originalArticleLength, $summarizedArticleLength];

            fputcsv($file, $row);
        }

        fclose($file);
    }

    private function getOriginalArticleLength(string $articleS3Filename): string
    {
        $originalArticleJson = $this->s3StorageService->readFromS3Bucket($articleS3Filename);

        $originalArticle = json_decode($originalArticleJson, true);
        if (!$originalArticle) {
            return '';
        }

        return (string) $this->getArticleLength($originalArticle['content']);
    }

    private function getArticleLength(string $articleContent): int
    {
        return mb_strlen($articleContent);
    }
}
