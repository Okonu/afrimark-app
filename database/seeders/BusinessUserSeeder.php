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
        $users = User::whereIn('email', [
            'ianyakundi015@gmail.com',
            'okonuian@gmail.com',
            'afrimark.ke@gmail.com',
        ])->get();

        $businesses = Business::take(3)->get();

        DB::table('business_users')->insert([
            'user_id' => $users->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $businesses[0]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $users->where('email', 'okonuian@gmail.com')->first()->id,
            'business_id' => $businesses[1]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $users->where('email', 'afrimark.ke@gmail.com')->first()->id,
            'business_id' => $businesses[2]->id,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $users->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $businesses[1]->id,
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('business_users')->insert([
            'user_id' => $users->where('email', 'ianyakundi015@gmail.com')->first()->id,
            'business_id' => $businesses[2]->id,
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
