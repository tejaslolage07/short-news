<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsHandler\NewsHandler;

class FetchNewsFromNewsDataIoWhenDBEmpty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:newsdataio-until-date
                            {initialDate : The initial date until which news articles will be fetched (format: Y-m-d))}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, summarize and store news from NewsData.io until date';

    /**
     * Execute the console command.
     */
    public function handle(NewsHandler $newsHandler)
    {
        $newsHandler->fetchAndStoreNewsFromNewsDataIo($this->argument('initialDate'));
        info('News fetched from NewsData.io');
    }
}
