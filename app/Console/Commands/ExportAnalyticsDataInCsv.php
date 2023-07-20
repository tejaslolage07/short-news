<?php

namespace App\Console\Commands;

use App\Services\ExportAnalyticsData;
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
    public function handle(ExportAnalyticsData $exportAnalyticsData)
    {
        $exportAnalyticsData->exportCsv();
        info('Analytics data exported in CSV format');
    }
}
