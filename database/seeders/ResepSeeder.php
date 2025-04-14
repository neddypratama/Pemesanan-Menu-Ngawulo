<?php

namespace Database\Seeders;

use App\Models\Resep;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (Resep::count() > 0) {
            return;
        }

        Resep::insert([
            // Coffee
            ['menu_id' => 1, 'resep' => '1 shot espresso, diseduh dengan air panas.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 2, 'resep' => '1 shot espresso, 150ml susu steamed, 50ml foam.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Non-Coffee
            ['menu_id' => 3, 'resep' => 'Cokelat bubuk 2 sdm, susu 200ml, gula secukupnya.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 4, 'resep' => 'Matcha bubuk 1 sdm, susu 200ml, madu secukupnya.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Tea
            ['menu_id' => 5, 'resep' => 'Teh hitam 1 sdt, air panas 200ml, gula sesuai selera.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 6, 'resep' => 'Teh hitam, air panas, lemon segar, gula sesuai selera.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Milkshake
            ['menu_id' => 7, 'resep' => 'Susu 200ml, es krim vanila, gula secukupnya, blender hingga lembut.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 8, 'resep' => 'Susu 200ml, es krim cokelat, cokelat bubuk, gula.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Juice
            ['menu_id' => 9, 'resep' => 'Jeruk segar diperas, tambahkan gula dan es batu.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 10, 'resep' => 'Alpukat matang, susu kental manis, es batu, blender hingga halus.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Snack
            ['menu_id' => 11, 'resep' => 'Kentang dipotong, digoreng hingga keemasan, disajikan dengan saus.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 12, 'resep' => 'Bawang dipotong cincin, dibalur tepung bumbu, digoreng renyah.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Dessert
            ['menu_id' => 13, 'resep' => 'Brownie cokelat dibuat dengan cokelat asli dan kacang walnut.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 14, 'resep' => 'Cheesecake dibuat dari keju krim, gula, dan biskuit sebagai dasar.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Main Course
            ['menu_id' => 15, 'resep' => 'Dada ayam dipanggang dengan saus BBQ dan kentang tumbuk.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 16, 'resep' => 'Pasta direbus dan disajikan dengan saus krim spesial.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Pastry
            ['menu_id' => 17, 'resep' => 'Croissant dibuat dari adonan berlapis dengan butter.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 18, 'resep' => 'Danish pastry dibuat dengan adonan renyah dan isian buah.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],

            // Special Menu
            ['menu_id' => 19, 'resep' => 'Burger dengan daging premium, keju, sayuran segar, dan saus spesial.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
            ['menu_id' => 20, 'resep' => 'Minuman spesial dengan kombinasi sirup rahasia dan buah segar.', 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
        ]);
    }
}
