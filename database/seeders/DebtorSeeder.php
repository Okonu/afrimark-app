<?php

namespace Database\Seeders;

use App\Models\Debtor;
use Illuminate\Database\Seeder;

class DebtorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Debtor::factory()->active()->count(40)->create();
        Debtor::factory()->pending()->count(30)->create();
        Debtor::factory()->paid()->count(15)->create();
        Debtor::factory()->disputed()->count(15)->create();
    }
}
