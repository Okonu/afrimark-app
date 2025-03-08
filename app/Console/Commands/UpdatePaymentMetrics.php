<?php

namespace App\Console\Commands;

use App\Models\BusinessDebtor;
use App\Models\Invoice;
use App\Services\Calculations\BusinessDebtorMetricsCalculator;
use App\Services\Calculations\InvoiceMetricsCalculator;
use Illuminate\Console\Command;

class UpdatePaymentMetrics extends Command
{
    protected $signature = 'metrics:update';
    protected $description = 'Update payment metrics for all invoices and business-debtor relationships';

    protected $invoiceMetricsCalculator;
    protected $businessDebtorMetricsCalculator;

    public function __construct(
        InvoiceMetricsCalculator $invoiceMetricsCalculator,
        BusinessDebtorMetricsCalculator $businessDebtorMetricsCalculator
    ) {
        parent::__construct();
        $this->invoiceMetricsCalculator = $invoiceMetricsCalculator;
        $this->businessDebtorMetricsCalculator = $businessDebtorMetricsCalculator;
    }

    public function handle()
    {
        $this->info('Updating invoice metrics...');
        $invoices = Invoice::all();

        $bar = $this->output->createProgressBar($invoices->count());
        $bar->start();

        foreach ($invoices as $invoice) {
            $this->invoiceMetricsCalculator->updateInvoiceMetrics($invoice);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('Updating business-debtor relationship metrics...');
        $businessDebtors = BusinessDebtor::all();

        $bar = $this->output->createProgressBar($businessDebtors->count());
        $bar->start();

        foreach ($businessDebtors as $bd) {
            $this->businessDebtorMetricsCalculator->updateBusinessDebtorMetrics($bd);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('All payment metrics have been updated!');
    }
}
