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
    protected $signature = 'fetch:newsdataio
                            {untilDate? : The date until which news articles will be fetched (format: Y-m-d))}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, summarize and store news from NewsData.io
                                (if no date is provided, null is considered.)
                                Arguments: untilDate=YYYY-MM-DD';

    /**
     * Execute the console command.
     */
    public function handle(NewsHandler $newsHandler)
    {
        $newsHandler->fetchAndStoreNewsFromNewsDataIo($this->argument('untilDate'));
        info('News fetched from NewsData.io');
    }
}
