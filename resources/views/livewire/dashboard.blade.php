<?php

use App\Models\Transaksi;
use App\Models\Order;
use App\Models\User;
use App\Models\Kategori;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public int $days = 30;

    public array $myChart = [];

    public array $categoryChart = [];

    public function mount()
    {
        $this->chartGross();
        $this->chartCategories();
    }

    public function chartGross()
    {
        $data = Transaksi::where('status', 'success')
            ->where('tanggal', '>=', Carbon::now()->subDays($this->days))
            ->orderBy('tanggal')
            ->get()
            ->groupBy(fn($trx) => Carbon::parse($trx->tanggal)->format('Y-m-d')) // Group by date
            ->map(fn($trx) => $trx->sum('total')) // Sum total per day
            ->toArray();

        $this->myChart = [
            'type' => 'line', // Bisa diganti 'bar' jika ingin tampilan batang
            'data' => [
                'labels' => array_keys($data),
                'datasets' => [
                    [
                        'label' => 'Gross Income',
                        'data' => array_values($data),
                        'borderColor' => '#4F46E5',
                        'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    ],
                ],
            ],
        ];
    }

    public function chartCategories()
    {
        $orders = Order::join('menus', 'orders.menu_id', '=', 'menus.id')
            ->join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id') // Join ke tabel transaksis
            ->selectRaw('menus.kategori_id, SUM(orders.qty) as total_qty') // Sum qty per kategori
            ->where('orders.created_at', '>=', Carbon::now()->subDays($this->days))
            ->groupBy('menus.kategori_id')
            ->get();

        $kategoriNames = Kategori::whereIn('id', $orders->pluck('kategori_id'))->pluck('name', 'id')->toArray();

        $data = $orders
            ->mapWithKeys(
                fn($order) => [
                    $kategoriNames[$order->kategori_id] ?? 'Unknown' => $order->total_qty, // Gunakan total qty
                ],
            )
            ->toArray();

        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0', '#F44336', '#E91E63', '#03A9F4', '#009688', '#FF9800'];

        $this->categoryChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($data),
                'datasets' => [
                    [
                        'label' => 'Total Quantity per Category',
                        'data' => array_values($data), // Total qty per kategori
                        'backgroundColor' => array_slice($colors, 0, count($data)),
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'cutout' => '60%', // Ukuran lubang tengah
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom', // Posisi label di bawah chart
                        'labels' => [
                            'usePointStyle' => true, // Gunakan titik warna di label
                            'pointStyle' => 'circle', // Bentuk ikon legenda
                        ],
                    ],
                ],
            ],
        ];
    }

    public function updatedDays()
    {
        $this->chartGross();
        $this->chartCategories();
        // $this->grossTotal();
        // $this->totalOrders();
        // $this->newCustomers();
        // $this->totalQty();
    }

    public function grossTotal(): float
    {
        return Transaksi::where('status', 'success')
            ->where('tanggal', '>=', Carbon::now()->subDays($this->days))
            ->sum('total');
    }

    public function totalOrders(): int
    {
        return Order::join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id') // Join ke tabel transaksis
            ->where('transaksis.status', 'success') // Hanya transaksi yang sukses
            ->where('orders.created_at', '>=', Carbon::now()->subDays($this->days))
            ->count();
    }

    public function newCustomers(): int
    {
        return User::where('role_id', 4)
            ->where('created_at', '>=', Carbon::now()->subDays($this->days))
            ->count();
    }

    public function totalQty(): int
    {
        return Order::join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id') // Join ke tabel transaksis
            ->where('transaksis.status', 'success') // Hanya transaksi sukses
            ->where('orders.created_at', '>=', Carbon::now()->subDays($this->days))
            ->sum('orders.qty'); // Hitung total qty
    }

    public function topCustomers()
    {
        return Transaksi::join('users', 'transaksis.user_id', '=', 'users.id')
            ->selectRaw('users.avatar, users.name, users.no_hp as phone_number, SUM(transaksis.total) as total_spent')
            ->where('transaksis.status', 'success') // Hanya transaksi sukses
            ->where('transaksis.tanggal', '>=', Carbon::now()->subDays($this->days))
            ->groupBy('users.id', 'users.name', 'users.no_hp', 'users.avatar')
            ->orderByDesc('total_spent')
            ->limit(3)
            ->get();
    }

    public function bestSellers()
    {
        return Order::join('menus', 'orders.menu_id', '=', 'menus.id')
            ->join('kategoris', 'menus.kategori_id', '=', 'kategoris.id')
            ->join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id') // Join ke transaksi
            ->selectRaw('menus.photo, kategoris.name as kategori_name, menus.name, SUM(orders.qty) as total_sold')
            ->where('transaksis.status', 'success') // Hanya transaksi sukses
            ->where('orders.created_at', '>=', Carbon::now()->subDays($this->days))
            ->groupBy('menus.id', 'menus.name', 'menus.photo', 'kategoris.name') // Tambahkan semua kolom SELECT ke GROUP BY
            ->orderByDesc('total_sold')
            ->limit(3)
            ->get();
    }

    public function with()
    {
        return [
            'grossTotal' => $this->grossTotal(),
            'totalOrders' => $this->totalOrders(),
            'newCustomers' => $this->newCustomers(),
            'totalQty' => $this->totalQty(),
            'topCustomers' => $this->topCustomers(),
            'bestSellers' => $this->bestSellers(),
            'days' => $this->days,
        ];
    }
};
?>

<div class="">
    <x-header title="Dashboard" separator progress-indicator>
        <x-slot:actions>
            <!-- Dropdown Select -->
            @php
                $days = [
                    [
                        'id' => 7,
                        'name' => 'Last 7 days',
                    ],
                    [
                        'id' => 15,
                        'name' => 'Last 15 days',
                    ],
                    [
                        'id' => 30,
                        'name' => 'Last 30 days',
                    ],
                ];
            @endphp

            <x-select label="" :options="$days" wire:model.live="days" />
        </x-slot:actions>
    </x-header>

    <!-- Grid Container -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Gross -->
        <x-card class=" rounded-lg shadow p-4">
            <div class="flex items-center justify-center gap-3">
                <x-icon name="fas.money-bill-wave" class="text-purple-500 w-10 h-10" />
                <div>
                    <p class="">Gross</p>
                    <p class="text-xl  font-bold">Rp. {{ number_format($grossTotal, 2) }}</p>
                </div>
            </div>
        </x-card>

        <!-- Orders -->
        <x-card class=" rounded-lg shadow p-4">
            <div class="flex items-center justify-center gap-3">
                <x-icon name="o-shopping-bag" class="text-blue-500 w-10 h-10" />
                <div>
                    <p class="">Orders</p>
                    <p class="text-xl  font-bold">{{ $totalOrders }}</p>
                </div>
            </div>
        </x-card>

        <!-- New Customers -->
        <x-card class=" rounded-lg shadow p-4">
            <div class="flex items-center justify-center gap-3">
                <x-icon name="o-user-plus" class="text-green-500 w-10 h-10" />
                <div>
                    <p class="">New Customers</p>
                    <p class="text-xl  font-bold">{{ $newCustomers }}</p>
                </div>
            </div>
        </x-card>

        <!-- Built with -->
        <x-card class=" rounded-lg shadow p-4">
            <div class="flex items-center justify-center gap-3">
                <x-icon name="o-gift" class="text-yellow-500 w-10 h-10" />
                <div>
                    <p class="">Quantity</p>
                    <p class="text-xl  font-bold">{{ $totalQty }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
        <x-card class="grid col-span-4">
            <x-slot:title>Gross</x-slot:title>
            <x-chart wire:model="myChart" />
        </x-card>

        <x-card>
            <x-slot:title>Category</x-slot:title>
            <x-chart wire:model="categoryChart" />
        </x-card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div class="card bg-base-100 rounded-lg p-5 shadow-sm" wire:key="mary5dbffb64b26e4af4d38ea523d42ca460">
            <div class="pb-5">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold ">
                            Top Customers
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- <a href="/users"
                            wire:key="mary6123c214ff04a06bfa9a075b72dd8096" type="button"
                            class="btn normal-case btn-ghost btn-sm" wire:navigate=""> --}}
                        <span class="">
                            Customers
                        </span>
                        <span class="block">
                            <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                            </svg>
                        </span>
                        {{-- </a> --}}
                    </div>
                </div>
                <hr class="mt-3">
            </div>
            <div>
                @foreach ($topCustomers as $customer)
                    <div>
                        <div class="flex justify-start items-center gap-4 px-3 hover:bg-base-200/50 cursor-pointer">
                            <div>
                                <a href="" wire:navigate="">
                                    <!-- AVATAR -->
                                    <div class="py-3">
                                        <div class="avatar">
                                            <div class="w-11 rounded-full">
                                                <img src="{{ $customer->avatar }}">
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- CONTENT -->
                            <div
                                class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0 mary-hideable">
                                <a href="" wire:navigate="">
                                    <div class="py-3">
                                        <div class="font-semibold truncate">
                                            {{ $customer->name }}
                                        </div>

                                        <div class="text-gray-400 text-sm truncate">
                                            {{ $customer->phone_number }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- ACTION -->
                            <a href="" wire:navigate="">
                                <div class="py-3 flex items-center gap-3 mary-hideable">
                                    <div class="badge font-bold">
                                        Rp. {{ number_format($customer->total_spent, 2) }}
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <div class="card bg-base-100 rounded-lg p-5 shadow-sm" wire:key="mary5dbffb64b26e4af4d38ea523d42ca460">
            <div class="pb-5">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold ">
                            Best Sellers
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- <a href="/users"
                                wire:key="mary6123c214ff04a06bfa9a075b72dd8096" type="button"
                                class="btn normal-case btn-ghost btn-sm" wire:navigate=""> --}}
                        <span class="">
                            Product
                        </span>
                        <span class="block">
                            <svg class="inline w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                                data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                            </svg>
                        </span>
                        {{-- </a> --}}
                    </div>
                </div>
                <hr class="mt-3">
            </div>
            <div>
                @foreach ($bestSellers as $menu)
                    <div>
                        <div class="flex justify-start items-center gap-4 px-3 hover:bg-base-200/50 cursor-pointer">
                            <div>
                                <a href="" wire:navigate="">
                                    <!-- AVATAR -->
                                    <div class="py-3">
                                        <div class="avatar">
                                            <div class="w-11 rounded-full">
                                                <img src="{{ $menu->photo }}">
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- CONTENT -->
                            <div
                                class="flex-1 overflow-hidden whitespace-nowrap text-ellipsis truncate w-0 mary-hideable">
                                <a href="" wire:navigate="">
                                    <div class="py-3">
                                        <div class="font-semibold truncate">
                                            {{ $menu->name }}
                                        </div>

                                        <div class="text-gray-400 text-sm truncate">
                                            {{ $menu->kategori_name }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <!-- ACTION -->
                            <a href="" wire:navigate="">
                                <div class="py-3 flex items-center gap-3 mary-hideable">
                                    <div class="badge font-bold">
                                        {{ $menu->total_sold }}
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </div>

    
</div>
