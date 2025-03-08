<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = [
            [
                'name' => 'Acme Corporation',
                'email' => 'info@acme.co.ke',
                'phone' => '+254700123456',
                'address' => '123 Main St, Nairobi',
                'registration_number' => 'A123456789X',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'XYZ Enterprises',
                'email' => 'contact@xyz.co.ke',
                'phone' => '+254711234567',
                'address' => '456 Market Ave, Mombasa',
                'registration_number' => 'B987654321Y',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Savanna Solutions',
                'email' => 'hello@savanna.co.ke',
                'phone' => '+254722345678',
                'address' => '789 Plaza Road, Kisumu',
                'registration_number' => 'C654321987Z',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($businesses as $business) {
            Business::create($business);
        }

        Business::factory()->count(47)->create();
    }
}
