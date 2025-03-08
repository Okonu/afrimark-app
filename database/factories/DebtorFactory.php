<?php

namespace Database\Factories;

use App\Models\Debtor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtorFactory extends Factory
{
    protected $model = Debtor::class;

    public function definition()
    {
        $emails = [
            'ianyakundi015@gmail.com',
            'huttech.ke@gmail.com',
            'afrimark.ke@gmail.com',
            'iokonu99@gmail.com',
            'ian.okonu@afrimark.io',
            'dev@afrimark.io',
        ];

        $baseEmail = $emails[array_rand($emails)];

        $plusEmail = str_replace('@', '+debtor' . $this->faker->numberBetween(1, 1000) . '@', $baseEmail);

        $statuses = ['active', 'pending', 'paid', 'disputed'];
        $status = $statuses[array_rand($statuses)];

        $now = now();
        $listingGoesLiveAt = null;
        $listedAt = null;

        if ($status === 'active') {
            $listedAt = $now->copy()->subDays($this->faker->numberBetween(1, 30));
        } elseif ($status === 'pending') {
            $listingGoesLiveAt = $now->copy()->addDays($this->faker->numberBetween(1, 30));
        }

        return [
            'name' => $this->faker->company,
            'kra_pin' => $this->faker->unique()->regexify('[A-Z][0-9]{9}[A-Z]'),
            'email' => $plusEmail,
            'status' => $status,
            'status_notes' => $status === 'disputed' || $status === 'paid' ? $this->faker->sentence : null,
            'status_updated_at' => $now->copy()->subDays($this->faker->numberBetween(1, 30)),
            'listing_goes_live_at' => $listingGoesLiveAt,
            'listed_at' => $listedAt,
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'listed_at' => now()->subDays($this->faker->numberBetween(1, 30)),
                'listing_goes_live_at' => null,
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'listed_at' => null,
                'listing_goes_live_at' => now()->addDays($this->faker->numberBetween(1, 30)),
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'status_notes' => 'Payment received in full',
                'listed_at' => now()->subDays($this->faker->numberBetween(30, 60)),
                'listing_goes_live_at' => null,
            ];
        });
    }

    public function disputed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'disputed',
                'status_notes' => $this->faker->randomElement([
                    'Invoice amount disputed',
                    'Service quality issues',
                    'Delivery discrepancies',
                    'Contract terms under review',
                    'Payment terms disagreement'
                ]),
                'listed_at' => null,
                'listing_goes_live_at' => null,
            ];
        });
    }
}
