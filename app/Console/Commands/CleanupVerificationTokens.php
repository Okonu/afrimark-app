<?php

namespace App\Console\Commands;

use App\Models\BusinessVerification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupVerificationTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old unused business verification tokens';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting verification token cleanup...');

        $count = BusinessVerification::where('created_at', '<', Carbon::now()->subDays(30))
            ->whereNull('verified_at')
            ->delete();

        $this->info("Successfully deleted {$count} expired verification tokens.");

        return Command::SUCCESS;
    }
}
