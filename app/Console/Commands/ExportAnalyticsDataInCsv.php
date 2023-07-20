<?php

namespace App\Console\Commands;

use App\Services\AnalyticsDataExporter;
use Illuminate\Console\Command;

class ExportAnalyticsDataInCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export-analytics:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the analytics data in CSV format';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsDataExporter $analyticsDataExporter)
    {
        $analyticsDataExporter->exportCsv();
        info('Analytics data exported in CSV format');
    }
}
