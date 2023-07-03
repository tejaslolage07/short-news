<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsHandler\NewsHandler;

class FetchNewsFromNewsDataIo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:newsdataio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, summarize and store news from NewsData.io';

    /**
     * Execute the console command.
     */
    public function handle(NewsHandler $newsHandler)
    {
        $newsHandler->fetchAndStoreNewsFromNewsDataIo(null);
        info('News fetched from NewsData.io');
    }
}
