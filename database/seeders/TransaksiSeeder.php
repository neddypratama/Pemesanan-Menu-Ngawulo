<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            DB::table('transaksis')->insert([
                'invoice' => 'INV-' . date('Ymd') . '-' . Str::random(4),
                'user_id' => rand(1, 10), // Pastikan ada user dengan ID 1-10
                'tanggal' => Carbon::now()->subDays(rand(0, 30)),
                'total' => rand(50000, 500000), // Total transaksi dalam rentang tertentu
                // 'no_meja' => rand(1, 10),
                'status' => ['success', 'pending', 'cancel'][rand(0, 2)], // Status acak
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }
    }
}
