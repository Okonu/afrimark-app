<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('payment_terms')->nullable();
            $table->integer('days_overdue')->nullable();
            $table->float('dbt_ratio', 8, 4)->nullable();
        });

        Schema::table('business_debtor', function (Blueprint $table) {
            $table->float('average_payment_terms', 8, 2)->nullable();
            $table->float('median_payment_terms', 8, 2)->nullable();
            $table->float('average_days_overdue', 8, 2)->nullable();
            $table->float('median_days_overdue', 8, 2)->nullable();
            $table->float('average_dbt_ratio', 8, 4)->nullable();
            $table->float('median_dbt_ratio', 8, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'payment_terms',
                'days_overdue',
                'dbt_ratio'
            ]);
        });

        Schema::table('business_debtor', function (Blueprint $table) {
            $table->dropColumn([
                'average_payment_terms',
                'median_payment_terms',
                'average_days_overdue',
                'median_days_overdue',
                'average_dbt_ratio',
                'median_dbt_ratio'
            ]);
        });
    }
};
