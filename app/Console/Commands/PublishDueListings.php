<?php

namespace App\Console\Commands;

use App\Services\Debtor\DebtorService;
use Illuminate\Console\Command;

class PublishDueListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debtors:publish-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish debtor listings that have passed their countdown period and have no active disputes';

    /**
     * Execute the console command.
     */
    public function handle(DebtorService $debtorService): int
    {
        $this->info('Starting to publish due listings...');

        $count = $debtorService->publishDueListings();

        $this->info("Successfully published {$count} listings.");

        return Command::SUCCESS;
    }
}
