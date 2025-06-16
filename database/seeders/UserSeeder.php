<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'role_id' => 1,
            'name' => 'admin',
            'bio' => 'administator',
            'email' => 'admin@gmail.com',
            'no_hp' => '08' . mt_rand(1000000000, 9999999999),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30)),
        ]);
        DB::table('users')->insert([
            'role_id' => 2,
            'name' => 'manager',
            'bio' => 'Manager',
            'email' => 'manager@gmail.com',
            'no_hp' => '08' . mt_rand(1000000000, 9999999999),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30)),
        ]);
        DB::table('users')->insert([
            'role_id' => 3,
            'name' => 'barista',
            'bio' => 'barista',
            'email' => 'barista@gmail.com',
            'no_hp' => '08' . mt_rand(1000000000, 9999999999),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30)),
        ]);
        DB::table('users')->insert([
            'role_id' => 4,
            'name' => 'pembeli',
            'bio' => 'pembeli',
            'email' => 'pembeli@gmail.com',
            'no_hp' => '08' . mt_rand(1000000000, 9999999999),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30)),
        ]);
        User::factory(count: 10)->create();
    }
}
