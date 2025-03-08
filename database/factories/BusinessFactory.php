<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition()
    {
        $emails = [
            'ianyakundi015@gmail.com',
            'okonuian@gmail.com',
            'afrimark.ke@gmail.com',
            'iokonu99@gmail.com',
            'ian.okonu@afrimark.io',
            'dev@afrimark.io',
        ];

        $baseEmail = $emails[array_rand($emails)];
        $plusEmail = str_replace('@', '+business' . $this->faker->numberBetween(1, 1000) . '@', $baseEmail);

        return [
            'name' => $this->faker->company,
            'email' => $plusEmail,
            'phone' => '+254' . $this->faker->numberBetween(700000000, 799999999),
            'address' => $this->faker->address,
            'registration_number' => $this->faker->unique()->regexify('[A-Z][0-9]{9}[A-Z]'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
