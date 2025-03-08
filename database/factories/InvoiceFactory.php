<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        $invoiceDate = now()->subDays($this->faker->numberBetween(1, 180));
        $paymentTerms = $this->faker->randomElement([7, 14, 30, 45, 60, 90]);
        $dueDate = (clone $invoiceDate)->addDays($paymentTerms);

        $invoiceAmount = $this->faker->randomFloat(2, 100, 5000);
        $dueAmount = $this->faker->randomElement([
            $invoiceAmount,
            $invoiceAmount * 0.75,
            $invoiceAmount * 0.5,
            $invoiceAmount * 0.25,
            0
        ]);

        $now = now();
        $daysOverdue = $now->gt($dueDate) ? $now->diffInDays($dueDate) : 0;
        $dbtRatio = $paymentTerms > 0 ? ($daysOverdue / $paymentTerms) : 0;

        return [
            'invoice_number' => 'INV-' . strtoupper($this->faker->bothify('??####')),
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'invoice_amount' => $invoiceAmount,
            'due_amount' => $dueAmount,
            'payment_terms' => $paymentTerms,
            'days_overdue' => $daysOverdue,
            'dbt_ratio' => $dbtRatio,
        ];
    }

    public function overdue()
    {
        return $this->state(function (array $attributes) {
            $invoiceDate = now()->subDays($this->faker->numberBetween(60, 180));
            $paymentTerms = $this->faker->randomElement([7, 14, 30, 45, 60]);
            $dueDate = (clone $invoiceDate)->addDays($paymentTerms);

            $daysOverdue = now()->diffInDays($dueDate);
            $dbtRatio = $daysOverdue / $paymentTerms;

            return [
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'days_overdue' => $daysOverdue,
                'dbt_ratio' => $dbtRatio,
            ];
        });
    }

    public function current()
    {
        return $this->state(function (array $attributes) {
            $invoiceDate = now()->subDays($this->faker->numberBetween(1, 30));
            $paymentTerms = $this->faker->randomElement([30, 45, 60, 90]);
            $dueDate = (clone $invoiceDate)->addDays($paymentTerms);

            return [
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'days_overdue' => 0,
                'dbt_ratio' => 0,
            ];
        });
    }
}
