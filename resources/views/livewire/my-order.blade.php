<?php

use App\Models\Transaksi;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.buy')] class extends Component {
    public $orders = [];

    public function mount()
    {
        $this->orders = Transaksi::with('orders.menu')->where('user_id', Auth::id())->latest()->get();
    }

    public function goToDetail($id)
    {
        return redirect()->route('orders.show', $id);
    }
};
?>
<div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
    <div class="lg:col-span-4 flex justify-center">
        <img src="https://orange.mary-ui.com/images/orders.png" alt="Order Image" class="w-full max-w-sm">
    </div>

    <div class="lg:col-span-6">
        <x-header title="Orders" separator progress-indicator />

        @forelse ($orders as $index => $order)
            <div wire:click="goToDetail({{ $order->id }})"
                class="cursor-pointer grid grid-cols-5 md:grid-cols-12 gap-4 bg-white dark:bg-base-200 rounded-lg shadow-sm p-4 md:p-5 border border-base-300 mb-4 hover:bg-base-100 transition">
                {{-- Nomor urut --}}
                <div class="md:col-span-9 col-span-4 flex gap-1">
                    <div class="text-sm font-semibold px-3 py-1 bg-base-200 rounded">
                        #{{ $order->id }}
                    </div>

                    {{-- Info pesanan --}}
                    <div class="flex-col">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold">{{ $order->orders->first()->menu->name ?? 'Unknown Item' }}</div>
                            @if ($order->orders->count() > 1)
                                <div class="text-sm text-gray-500 font-medium">
                                    + {{ $order->orders->count() - 1 }} item(s)
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-3 align-center items-center">
                            <div class="text-sm text-gray-500">
                                {{ $order->created_at->format('Y-m-d') }}
                            </div>
                            <div class="text-lg font-bold text-gray-500">
                                Rp. {{ number_format($order->total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="md:col-span-3 col-span-1 flex justify-start md:justify-end items-center md:items-center">
                    @php
                        $status = strtolower($order->status ?? 'pending');

                        $iconMap = [
                            'success' => 'check-circle',
                            'pending' => 'clock',
                            'error' => 'x-circle',
                            'cancel' => 'minus-circle',
                            'expire' => 'x-circle',
                            'delivered' => 'truck',
                            'done' => 'check-badge',
                        ];

                        $icon = $iconMap[$status] ?? 'question-mark-circle';
                    @endphp

                    {{-- Icon untuk mobile --}}
                    <x-icon :name="'o-' . $icon" class="w-6 h-6 text-gray-500 md:hidden" />

                    {{-- Badge untuk desktop --}}
                    @if ($status === 'new')
                        <x-badge value="Pesanan berhasil" class="bg-orange-400 dark:text-black hidden md:inline" />
                    @elseif ($status === 'success')
                        <x-badge value="Pembayaran berhasil" class="bg-green-200 dark:text-black hidden md:inline" />
                    @elseif ($status === 'pending')
                        <x-badge value="Menunggu pembayaran" class="bg-yellow-200 dark:text-black hidden md:inline" />
                    @elseif ($status === 'error' || $status === 'expire')
                        <x-badge value="Pembayaran gagal" class="bg-red-200 dark:text-black hidden md:inline" />
                    @elseif ($status === 'cancel')
                        <x-badge value="Pesanan dibatalkan" class="bg-gray-300 dark:text-black hidden md:inline" />
                    @elseif ($status === 'deliver')
                        <x-badge value="Pesanan dikirim" class="bg-blue-200 dark:text-black hidden md:inline" />
                    @elseif ($status === 'done')
                        <x-badge value="Pesanan selesai" class="bg-purple-200 dark:text-black hidden md:inline" />
                    @else
                        <x-badge value="Status tidak diketahui" class="bg-gray-200 dark:text-black hidden md:inline" />
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 dark:text-gray-400 mt-6">
                Kamu belum memiliki pesanan.
            </div>
        @endforelse
    </div>
</div>
