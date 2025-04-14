<?php
use App\Models\Transaksi;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.buy')] class extends Component {
    public $transaksi;

    public function mount(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
    }
}; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    {{-- Gambar ilustrasi --}}
    <div class="lg:col-span-3 flex justify-center items-start">
        <img src="https://orange.mary-ui.com/images/orders.png" alt="Order Image" class="w-full max-w-xs">
    </div>

    {{-- Detail pesanan --}}
    <div class="lg:col-span-5 space-y-6">
        <x-card class="rounded-xl border border-base-300 shadow-sm" title="Order #{{ $transaksi->id }}">
            <hr>
            @foreach ($transaksi->orders as $item)
                <div class="flex items-center justify-between mb-3 mt-6">
                    <div class="flex items-center gap-4">
                        <img src="{{ $item->menu->photo }}" class="w-12 h-12 object-cover rounded" alt="Menu Image">
                        <div>
                            <div class="font-semibold">{{ $item->menu->name }}</div>
                            <div class="text-sm text-gray-500">Rp. {{ number_format($item->menu->price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm font-bold bg-black text-white p-2 rounded">x {{ $item->qty }}</div>
                </div>
            @endforeach

            <hr class="my-4">

            <div class="flex justify-between font-bold text-lg">
                <div> Total </div>
                <div>Rp. {{ number_format($transaksi->total, 0, ',', '.') }}</div>
            </div>
        </x-card>
    </div>

    {{-- Timeline status --}}
    <div class="lg:col-span-4 space-y-4">
        <x-card class="rounded-xl border border-base-300 shadow-sm" title="Status">
            <hr class="mb-6">
            <div class="px-4">
                <x-timeline-item title="Order placed" icon="o-map-pin" first
                    subtitle="{{ $transaksi->created_at->format('Y-m-d H:i:s') }}"
                    description="We received the order, waiting for payment confirmation" :completed="true" />

                <x-timeline-item title="Payment confirmed" icon="o-credit-card" subtitle="{{ $transaksi->created_at->format('Y-m-d H:i:s') }}"
                    description="The payment was confirmed, preparing to ship." :completed="in_array($transaksi->status, ['delivered'])" />

                <x-timeline-item title="Shipped" icon="o-paper-airplane" subtitle="{{ $transaksi->created_at->format('Y-m-d H:i:s') }}"
                    description="The order was sent to courier." :completed="($transaksi->status === 'delivered')" />

                <x-timeline-item title="Delivered" icon="o-gift" subtitle="{{ $transaksi->created_at->format('Y-m-d H:i:s') }}"
                    description="The order was delivered successfully." :completed="($transaksi->status === 'delivered')" last />
            </div>
        </x-card>

    </div>
</div>
