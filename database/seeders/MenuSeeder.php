<?php

namespace Database\Seeders;

use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Menu::count() > 0) {
            return;
        }

        Menu::insert([
                // Coffee
                ['photo' => 'images/espresso.jpg', 'name' => 'Espresso', 'price' => 15000, 'deskripsi' => 'Espresso single shot dengan rasa khas.', 'stok' => 10, 'kategori_id' => 1, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/cappuccino.jpg', 'name' => 'Cappuccino', 'price' => 25000, 'deskripsi' => 'Kombinasi espresso, susu, dan foam.', 'stok' => 10, 'kategori_id' => 1, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Non-Coffee
                ['photo' => 'images/chocolate.jpg', 'name' => 'Hot Chocolate', 'price' => 20000, 'deskripsi' => 'Cokelat panas dengan susu.', 'stok' => 10, 'kategori_id' => 2, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/matcha.jpg', 'name' => 'Matcha Latte', 'price' => 25000, 'deskripsi' => 'Minuman matcha khas Jepang.', 'stok' => 10, 'kategori_id' => 2, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Tea
                ['photo' => 'images/black-tea.jpg', 'name' => 'Black Tea', 'price' => 15000, 'deskripsi' => 'Teh hitam pekat dengan aroma khas.', 'stok' => 10, 'kategori_id' => 3, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/lemon-tea.jpg', 'name' => 'Lemon Tea', 'price' => 18000, 'deskripsi' => 'Teh segar dengan perasan lemon.', 'stok' => 10, 'kategori_id' => 3, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Milkshake
                ['photo' => 'images/vanilla-shake.jpg', 'name' => 'Vanilla Milkshake', 'price' => 25000, 'deskripsi' => 'Milkshake vanila dengan whipped cream.', 'stok' => 10, 'kategori_id' => 4, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/choco-shake.jpg', 'name' => 'Chocolate Milkshake', 'price' => 26000, 'deskripsi' => 'Milkshake cokelat dengan rasa creamy.', 'stok' => 10, 'kategori_id' => 4, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Juice
                ['photo' => 'images/orange-juice.jpg', 'name' => 'Orange Juice', 'price' => 20000, 'deskripsi' => 'Jus jeruk segar.', 'stok' => 10, 'kategori_id' => 5, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/avocado-juice.jpg', 'name' => 'Avocado Juice', 'price' => 22000, 'deskripsi' => 'Jus alpukat dengan susu kental manis.', 'stok' => 10, 'kategori_id' => 5, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Snack
                ['photo' => 'images/french-fries.jpg', 'name' => 'French Fries', 'price' => 18000, 'deskripsi' => 'Kentang goreng dengan saus.', 'stok' => 10, 'kategori_id' => 6, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/onion-rings.jpg', 'name' => 'Onion Rings', 'price' => 19000, 'deskripsi' => 'Cincin bawang goreng renyah.', 'stok' => 10, 'kategori_id' => 6, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Dessert
                ['photo' => 'images/brownie.jpg', 'name' => 'Chocolate Brownies', 'price' => 25000, 'deskripsi' => 'Brownie cokelat dengan topping.', 'stok' => 10, 'kategori_id' => 7, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/cheesecake.jpg', 'name' => 'Cheesecake', 'price' => 27000, 'deskripsi' => 'Kue keju lembut dengan topping buah.', 'stok' => 10, 'kategori_id' => 7, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Main Course
                ['photo' => 'images/chicken-steak.jpg', 'name' => 'Chicken Steak', 'price' => 40000, 'deskripsi' => 'Steak ayam dengan saus BBQ.', 'stok' => 10, 'kategori_id' => 8, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/pasta.jpg', 'name' => 'Creamy Pasta', 'price' => 35000, 'deskripsi' => 'Pasta creamy dengan saus spesial.', 'stok' => 10, 'kategori_id' => 8, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Pastry
                ['photo' => 'images/croissant.jpg', 'name' => 'Croissant', 'price' => 20000, 'deskripsi' => 'Roti croissant dengan tekstur lembut.', 'stok' => 10, 'kategori_id' => 9, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/danish.jpg', 'name' => 'Danish Pastry', 'price' => 22000, 'deskripsi' => 'Pastry dengan isian buah dan custard.', 'stok' => 10, 'kategori_id' => 9, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
    
                // Special Menu
                ['photo' => 'images/special-burger.jpg', 'name' => 'Special Burger', 'price' => 35000, 'deskripsi' => 'Burger spesial dengan daging tebal.', 'stok' => 10, 'kategori_id' => 10, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
                ['photo' => 'images/special-drink.jpg', 'name' => 'Signature Drink', 'price' => 30000, 'deskripsi' => 'Minuman spesial racikan kafe.', 'stok' => 10, 'kategori_id' => 10, 'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),],
        ]);
    }
}
