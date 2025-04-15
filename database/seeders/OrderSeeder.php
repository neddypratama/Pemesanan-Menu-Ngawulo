<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transaksis = DB::table('transaksis')->get();

        foreach ($transaksis as $transaksi) {
            // Ambil 1 sampai 4 menu secara acak
            $menus = DB::table('menus')->inRandomOrder()->limit(rand(1, 4))->get();

            foreach ($menus as $menu) {
                DB::table('orders')->insert([
                    'menu_id' => $menu->id,
                    'qty' => rand(1, 5),
                    'keterangan' => 'Transaksi contoh ke-' . $menu->id,
                    'transaksi_id' => $transaksi->id,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}
