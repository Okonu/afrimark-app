<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specificUsers = User::whereIn('email', [
            'ianyakundi015@gmail.com',
            'huttech.ke@gmail.com',
            'afrimark.ke@gmail.com',
        ])->get();

        $originalBusinesses = Business::take(3)->get();

        DB::table('business_users')->insert([
            'user_id' => $specificUsers->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $originalBusinesses[0]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $specificUsers->where('email', 'huttech.ke@gmail.com')->first()->id,
            'business_id' => $originalBusinesses[1]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $specificUsers->where('email', 'afrimark.ke@gmail.com')->first()->id,
            'business_id' => $originalBusinesses[2]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $specificUsers->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $originalBusinesses[1]->id,
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $specificUsers->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $originalBusinesses[2]->id,
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $remainingBusinesses = Business::skip(3)->take(47)->get();
        $availableUsers = User::whereNotIn('email', [
            'ianyakundi015@gmail.com',
            'huttech.ke@gmail.com',
            'afrimark.ke@gmail.com',
        ])->get();

        $userIndex = 0;
        $roles = ['admin', 'user', 'viewer'];

        foreach ($remainingBusinesses as $business) {
            DB::table('business_users')->insert([
                'user_id' => $availableUsers[$userIndex]->id,
                'business_id' => $business->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userIndex++;

            if (rand(0, 1) === 1 && $userIndex < count($availableUsers)) {
                DB::table('business_users')->insert([
                    'user_id' => $availableUsers[$userIndex]->id,
                    'business_id' => $business->id,
                    'role' => $roles[array_rand(['user', 'viewer'])],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userIndex++;
            }

            if (rand(0, 3) === 0 && $userIndex < count($availableUsers)) {
                DB::table('business_users')->insert([
                    'user_id' => $availableUsers[$userIndex]->id,
                    'business_id' => $business->id,
                    'role' => $roles[array_rand(['user', 'viewer'])],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userIndex++;
            }

            if ($userIndex >= count($availableUsers) - 5) {
                $userIndex = 0;
            }
        }

        $randomUsers = $availableUsers->random(10);

        foreach ($randomUsers as $user) {
            $additionalBusinessCount = rand(2, 4);
            $randomBusinesses = Business::inRandomOrder()->take($additionalBusinessCount)->get();

            foreach ($randomBusinesses as $business) {
                $exists = DB::table('business_users')
                    ->where('user_id', $user->id)
                    ->where('business_id', $business->id)
                    ->exists();

                if (!$exists) {
                    DB::table('business_users')->insert([
                        'user_id' => $user->id,
                        'business_id' => $business->id,
                        'role' => $roles[array_rand($roles)],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
