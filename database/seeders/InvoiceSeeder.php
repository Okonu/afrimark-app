<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Invoice Seeder...');

        // Set a reasonable target number of invoices
        $maxInvoices = 1000;
        $invoiceCount = 0;

        // Get all business-debtor relationships
        $relationships = DB::table('business_debtor')
            ->select('business_id', 'debtor_id', 'id')
            ->limit(200) // Limit to first 200 relationships for faster seeding
            ->get();

        $this->command->info('Processing ' . count($relationships) . ' business-debtor relationships');

        // Process each relationship
        foreach ($relationships as $relationship) {
            // Create 1-3 invoices per relationship
            $invoicesPerRelationship = rand(10, 20);

            for ($i = 0; $i < $invoicesPerRelationship; $i++) {
                if ($invoiceCount >= $maxInvoices) {
                    $this->command->info("Reached maximum invoice count of {$maxInvoices}");
                    break 2; // Break out of both loops
                }

                // Generate basic invoice data
                $invoiceDate = now()->subDays(rand(1, 180));
                $paymentTerms = rand(7, 90);
                $dueDate = (clone $invoiceDate)->addDays($paymentTerms);

                $invoiceAmount = rand(10000, 100000) / 100; // Between 100.00 and 1,000.00
                $dueAmount = $invoiceAmount; // All unpaid for simplicity

                // Calculate metrics
                $daysOverdue = now()->gt($dueDate) ? now()->diffInDays($dueDate)*-1 : 0;
                $dbtRatio = $paymentTerms > 0 ? ($daysOverdue / $paymentTerms) : 0;

                // Create invoice record
                $invoiceNumber = 'INV-' . strtoupper(substr(md5(rand()), 0, 8));

                DB::table('invoices')->insert([
                    'business_id' => $relationship->business_id,
                    'debtor_id' => $relationship->debtor_id,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'invoice_amount' => $invoiceAmount,
                    'due_amount' => $dueAmount,
                    'payment_terms' => $paymentTerms,
                    'days_overdue' => $daysOverdue,
                    'dbt_ratio' => $dbtRatio,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $invoiceCount++;

                if ($invoiceCount % 100 === 0) {
                    $this->command->info("Created {$invoiceCount} invoices so far");
                }
            }
        }

        // Update all business_debtor relationships with the correct totals
        $this->command->info('Updating all business-debtor relationship amounts');

        // Get all unique business-debtor relationship IDs from the invoices table
        $allRelationships = DB::table('invoices')
            ->select('business_id', 'debtor_id')
            ->distinct()
            ->get();

        $updatedRelationships = 0;

        foreach ($allRelationships as $relationship) {
            // Calculate the total due amount for this business-debtor pair
            $totalDueAmount = DB::table('invoices')
                ->where('business_id', $relationship->business_id)
                ->where('debtor_id', $relationship->debtor_id)
                ->sum('due_amount');

            // Update the amount_owed in the business_debtor table
            DB::table('business_debtor')
                ->where('business_id', $relationship->business_id)
                ->where('debtor_id', $relationship->debtor_id)
                ->update([
                    'amount_owed' => $totalDueAmount,
                    'updated_at' => now(),
                ]);

            $updatedRelationships++;

            if ($updatedRelationships % 50 === 0) {
                $this->command->info("Updated {$updatedRelationships} relationship amounts");
            }
        }

        $this->command->info("Invoice seeding completed. Created {$invoiceCount} invoices and updated {$updatedRelationships} relationship amounts.");
    }
}