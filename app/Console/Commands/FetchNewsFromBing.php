<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsFetcherService;

class FetchNewsFromBing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:bing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, summarize and store news from Bing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new NewsFetcherService();
        $service->fetchAndStoreNewsFromBing();
    }
}
