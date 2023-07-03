<?php

namespace App\Console\Commands;

use App\Services\NewsHandler\NewsFetcher\NewsFetcherForNewsDataIo;
use Illuminate\Console\Command;
use App\Services\NewsHandler\NewsHandler;
use App\Services\NewsHandler\NewsParser\NewsParserForNewsDataIo;

class FetchNewsFromNewsDataIo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:newsdataio-when-db-empty 
                            {initialLimitDays : The number of days to fetch when DB is empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, summarize and store news from NewsData.io when Database is empty';

    /**
     * Execute the console command.
     */
    public function handle(NewsHandler $newsHandler)
    {
        $newsHandler->fetchAndStoreNewsFromNewsDataIoWhenDBEmpty($this->argument('initialLimitDays'));
        info('News fetched from NewsData.io');
    }
}
