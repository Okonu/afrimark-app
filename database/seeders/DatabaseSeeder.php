<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BusinessSeeder::class,
            DebtorSeeder::class,
            BusinessUserSeeder::class,
            BusinessDebtorSeeder::class,
            InvoiceSeeder::class,
            BusinessDebtorMetricsSeeder::class,
        ]);
    }
}
