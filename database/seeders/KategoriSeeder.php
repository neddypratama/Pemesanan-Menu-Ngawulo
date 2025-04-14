<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Kategori::count() > 0) {
            return;
        }

        Kategori::insert([
            ['image' => 'images/coffee.jpg', 'name' => 'Coffee', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/non-coffee.jpg', 'name' => 'Non-Coffee', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/tea.jpg', 'name' => 'Tea', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/milkshake.jpg', 'name' => 'Milkshake', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/juice.jpg', 'name' => 'Juice', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/snack.jpg', 'name' => 'Snack', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/dessert.jpg', 'name' => 'Dessert', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/main-course.jpg', 'name' => 'Main Course', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/pastry.jpg', 'name' => 'Pastry', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
            ['image' => 'images/special-menu.jpg', 'name' => 'Special Menu', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)), ],
        ]);
    }
}
