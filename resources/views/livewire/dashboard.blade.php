<?php

namespace App\Livewire;

use App\Models\Transaksi;
use App\Models\Order;
use App\Models\User;
use App\Models\Kategori;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public string $period = 'month';
    public $startDate;
    public $endDate;
    public array $myChart = [];
    public array $categoryChart = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->setDefaultDates();
        $this->chartGross();
        $this->chartCategories();
    }

    protected function setDefaultDates()
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'today':
                $this->startDate = $now->copy()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case 'week':
                $this->startDate = $now->copy()->startOfWeek();
                $this->endDate = $now->copy()->endOfWeek();
                break;
            case 'month':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
                break;
            case 'year':
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
                break;
            default:
                $this->startDate = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : $now->copy()->startOfMonth();
                $this->endDate = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : $now->copy()->endOfMonth();
        }
    }

    public function updatedPeriod()
    {
        $this->setDefaultDates();
        $this->chartGross();
        $this->chartCategories();
    }

    public function applyDateRange()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $this->period = 'custom';
        $this->startDate = Carbon::parse($this->startDate)->startOfDay();
        $this->endDate = Carbon::parse($this->endDate)->endOfDay();

        $this->chartGross();
        $this->chartCategories();
        $this->toast('Periode tanggal berhasil diperbarui', 'success');
    }

    public function chartGross()
    {
        $data = Transaksi::whereNotIn('status', ['pending', 'cancel'])
            ->whereBetween('tanggal', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->orderBy('tanggal')
            ->get()
            ->groupBy(fn($trx) => Carbon::parse($trx->tanggal)->format('Y-m-d'))
            ->map(fn($trx) => $trx->sum('total'))
            ->toArray();

        $this->myChart = [
            'type' => 'line',
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
            ->join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id')
            ->whereNotIn('transaksis.status', ['pending', 'cancel'])
            ->selectRaw('menus.kategori_id, SUM(orders.qty) as total_qty')
            ->whereBetween('orders.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->groupBy('menus.kategori_id')
            ->get();

        $kategoriNames = Kategori::whereIn('id', $orders->pluck('kategori_id'))->pluck('name', 'id')->toArray();

        $data = $orders->mapWithKeys(fn($order) => [$kategoriNames[$order->kategori_id] ?? 'Unknown' => $order->total_qty])->toArray();

        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0', '#F44336', '#E91E63', '#03A9F4', '#009688', '#FF9800'];

        $this->categoryChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($data),
                'datasets' => [
                    [
                        'label' => 'Total Quantity per Category',
                        'data' => array_values($data),
                        'backgroundColor' => array_slice($colors, 0, count($data)),
                    ],
                ],
            ],
        ];
    }

    public function grossTotal(): float
    {
        return Transaksi::whereNotIn('status', ['pending', 'cancel'])
            ->whereBetween('tanggal', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->sum('total');
    }

    public function totalOrders(): int
    {
        return Order::join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id')
            ->whereNotIn('transaksis.status', ['pending', 'cancel'])
            ->whereBetween('orders.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->count();
    }

    public function newCustomers(): int
    {
        return User::where('role_id', 4)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();
    }

    public function totalQty(): int
    {
        return Order::join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id')
            ->whereNotIn('transaksis.status', ['pending', 'cancel'])
            ->whereBetween('orders.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->sum('orders.qty');
    }

    public function topCustomers()
    {
        return Transaksi::join('users', 'transaksis.user_id', '=', 'users.id')
            ->selectRaw('users.avatar, users.name, users.no_hp as phone_number, SUM(transaksis.total) as total_spent')
            ->whereNotIn('transaksis.status', ['pending', 'cancel'])
            ->whereBetween('transaksis.tanggal', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->groupBy('users.id', 'users.name', 'users.no_hp', 'users.avatar')
            ->orderByDesc('total_spent')
            ->limit(3)
            ->get();
    }

    public function bestSellers()
    {
        return Order::join('menus', 'orders.menu_id', '=', 'menus.id')
            ->join('kategoris', 'menus.kategori_id', '=', 'kategoris.id')
            ->join('transaksis', 'orders.transaksi_id', '=', 'transaksis.id')
            ->selectRaw('menus.photo, kategoris.name as kategori_name, menus.name, SUM(orders.qty) as total_sold')
            ->whereNotIn('transaksis.status', ['pending', 'cancel'])
            ->whereBetween('orders.created_at', [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()])
            ->groupBy('menus.id', 'menus.name', 'menus.photo', 'kategoris.name')
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
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }
};
?>

<div class="">
    <x-header title="Dashboard" separator progress-indicator>
        <x-slot:actions>
            @php
                $periods = [
                    [
                        'id' => 'today',
                        'name' => 'Hari Ini',
                        'hint' => 'Data dalam 24 jam terakhir',
                        'icon' => 'o-clock',
                    ],
                    [
                        'id' => 'week',
                        'name' => 'Minggu Ini',
                        'hint' => 'Data minggu berjalan',
                        'icon' => 'o-calendar-days',
                    ],
                    [
                        'id' => 'month',
                        'name' => 'Bulan Ini',
                        'hint' => 'Data bulan berjalan',
                        'icon' => 'o-chart-pie',
                    ],
                    [
                        'id' => 'year',
                        'name' => 'Tahun Ini',
                        'hint' => 'Data tahun berjalan',
                        'icon' => 'o-chart-bar',
                    ],
                    [
                        'id' => 'custom',
                        'name' => 'Custom',
                        'hint' => 'Pilih rentang tanggal khusus',
                        'icon' => 'o-calendar',
                    ],
                ];
            @endphp

            @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                <div class="flex flex-col gap-4">
                    <x-select wire:model.live="period" :options="$periods" option-label="name" option-value="id"
                        option-description="hint" class="gap-4">
                    </x-select>

                    @if ($period === 'custom')
                        <div class="flex flex-col gap-4 mt-2">
                            <form wire:submit.prevent="applyDateRange">
                                <div class="flex flex-col md:flex-row gap-4 items-start md:items-end">
                                    <x-input type="date" label="Dari Tanggal" wire:model="startDate"
                                        :max="now()->format('Y-m-d')" class="w-full md:w-auto" />

                                    <x-input type="date" label="Sampai Tanggal" wire:model="endDate"
                                        :min="$startDate" :max="now()->format('Y-m-d')" class="w-full md:w-auto" />

                                    <x-button spinner label="Terapkan" type="submit" icon="o-check"
                                        class="btn-primary mt-2 md:mt-6 w-full md:w-auto" />
                                </div>

                                @error('endDate')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror

                                <div class="text-sm text-gray-500 mt-2">
                                    Periode terpilih:
                                    {{ $startDate->translatedFormat('d M Y') }} -
                                    {{ $endDate->translatedFormat('d M Y') }}
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </x-slot:actions>
    </x-header>

    <!-- Grid Container -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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

    @if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
            <x-card class="grid col-span-3">
                <x-slot:title>Gross</x-slot:title>
                <x-chart wire:model="myChart" />
            </x-card>

            <x-card class="grid col-span-2">
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
                            <div
                                class="flex justify-start items-center gap-4 px-3 hover:bg-base-200/50 cursor-pointer">
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
    @endif
</div>
