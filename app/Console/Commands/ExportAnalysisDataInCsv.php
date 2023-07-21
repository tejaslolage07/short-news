<?php

namespace App\Console\Commands;

use App\Services\AnalysisDataExporter;
use Illuminate\Console\Command;

class ExportAnalysisDataInCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export-analysis-data:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the analysis data in CSV format';

    /**
     * Execute the console command.
     */
    public function handle(AnalysisDataExporter $analysisDataExporter)
    {
        $analysisDataExporter->exportCsv();
        info('Analysis data exported in CSV format');
    }
}
