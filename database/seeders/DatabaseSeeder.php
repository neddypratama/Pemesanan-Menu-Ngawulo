<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(CountrySeeder::class);
        // $this->call(LanguageSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(KategoriSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(ResepSeeder::class);
        $this->call(TransaksiSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(RatingSeeder::class);
    }
}
