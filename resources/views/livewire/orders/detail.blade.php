<?php

use Livewire\Volt\Component;
use App\Models\Transaksi;

new class extends Component {
    public Transaksi $transaksi;

    public function mount(Transaksi $transaksi): void
    {
        $this->transaksi = $transaksi->load(['user', 'orders.menu']);
    }
};
?>

<div>
    <x-header title="View {{ $transaksi->invoice }}" separator progress-indicator />

    <x-card>
        <div class="p-7 mt-2 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class=" mb-3">Invoice</p>
                    <p class="font-semibold">{{ $transaksi->invoice }}</p>
                </div>
                <div>
                    <p class=" mb-3">Status</p>
                    @php
                        $colors = [
                            'success' => 'border-green-500 text-green-500',
                            'pending' => 'border-yellow-500 text-yellow-500',
                            'cancel' => 'border-red-500 text-red-500',
                        ];
                    @endphp
    
                    <span
                        class="px-2 py-1 border rounded-md {{ $colors[$transaksi['status']] ?? 'border-gray-300 text-gray-500' }}">
                        {{ ucfirst($transaksi['status']) }}
                    </span>
                </div>
                <div>
                    <p class=" mb-3">Created At</p>
                    <p class="font-semibold">{{ $transaksi->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
        <div class="p-7 mt-4 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class=" mb-3">Customer Name</p>
                    <p class="font-semibold">{{ $transaksi->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class=" mb-3">Email Address</p>
                    <p class="font-semibold">{{ $transaksi->user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class=" mb-3">Phone Number</p>
                    <p class="font-semibold">{{ $transaksi->user->no_hp ?? '-' }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-7 mt-4 rounded-lg shadow-md">
            <div>
                <p class="mb-3">Items Details</p>
                @foreach ($transaksi->orders as $order)
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-3 rounded-lg p-5">
                        <div>
                            <p class="mb-3">Product Image</p>
                            <img src="{{ $order->menu->photo }}" class="w-16 h-16 object-cover rounded">
                        </div>
                        <div>
                            <p class="mb-3">Product Name</p>
                            <p class="font-semibold">{{ $order->menu->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="mb-3">Quantity</p>
                            <p class="font-semibold">{{ $order->qty ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="mb-3">Price</p>
                            <p class="font-semibold">Rp. {{ number_format($order->menu->price, 0, ',', '.') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="mb-3">Note</p>
                            <p class="font-semibold ">{{ $order->keterangan ?? '-' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="p-7 mt-4 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <div>
                    <p class=" mb-3">Grand Total</p>
                    <p class="font-semibold text-end text-yellow-500 text-xl">Rp.
                        {{ number_format($transaksi->total, 0, ',', '.') ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </x-card>
            
    <div class="mt-6">
        <x-button label="Kembali" link="/orders" />
    </div>
</div>
