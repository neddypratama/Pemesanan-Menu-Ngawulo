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
        User::factory(50)->create();
    }
}
