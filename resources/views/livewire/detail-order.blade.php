<?php

use App\Models\Transaksi;
use App\Models\Rating;
use App\Models\Order;
use App\Models\ActivityLog;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.buy')] class extends Component {
    public Transaksi $transaksi;

    public bool $createModal = false;
    public array $newReview = [];
    public array $newRating = [];

    public function mount(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function create(): void
    {
        foreach ($this->transaksi->orders as $item) {
            $menuId = $item->menu->id;
            $this->newRating[$menuId] = 0;
            $this->newReview[$menuId] = '';
        }

        $this->createModal = true;
    }

    public function saveCreate(): void
    {
        $this->validate([
            'newRating' => 'required|array',
            'newRating.*' => 'integer|between:1,5',
            'newReview' => 'sometimes|array',
            'newReview.*' => 'string|nullable',
        ]);

        foreach ($this->transaksi->orders as $item) {
            $menuId = $item->menu->id;
            $rating = $this->newRating[$menuId] ?? 0;
            $review = $this->newReview[$menuId] ?? '';

            $rating = Rating::create([
                'menu_id' => $menuId,
                'rating' => $rating,
                'review' => $review,
            ]);
            Transaksi::findOrFail($this->transaksi->id)->update([
                'status' => 'reviewed',
            ]);
        }

        // Refresh data transaksi
        $this->transaksi->refresh();
        $this->transaksi->load('orders.menu');

        logActivity('created', $rating->id . ' ditambahkan');

        $this->createModal = false;
        session()->flash('status', 'Rating berhasil ditambahkan!');
    }

    public function markAsDone(): void
    {
        $this->transaksi->update([
            'status' => 'done',
            'updated_at' => now(),
        ]);

        logActivity('done', 'Merubah status done transaksi ' . $this->transaksi->invoice);

        $this->transaksi->refresh();
        $this->transaksi->load('orders.menu');

        session()->flash('status', 'Pesanan berhasil ditandai sebagai selesai.');
    }
};
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    @if (session('status') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
            class="fixed top-5 right-5 z-50 max-w-xs w-full p-4 rounded-lg shadow text-sm text-white transition duration-300"
            :class="{
                'bg-green-500': '{{ session('status') }}',
                'bg-red-500': '{{ session('error') }}'
            }"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2">
            <div class="flex justify-between items-center">
                <span class="flex-1">
                    {{ session('status') ?? session('error') }}
                </span>
                <button @click="show = false" class="ml-2">
                    &times;
                </button>
            </div>
        </div>
    @endif
    {{-- Gambar --}}
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
                            <div class="text-sm text-gray-500">
                                Rp. {{ number_format($item->menu->price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm font-bold bg-black text-white p-2 rounded">x {{ $item->qty }}</div>
                </div>
            @endforeach

            <hr class="my-4">

            <div class="flex justify-between font-bold text-lg">
                <div>Total</div>
                <div>Rp. {{ number_format($transaksi->total, 0, ',', '.') }}</div>
            </div>
        </x-card>
    </div>

    {{-- Status --}}
    @php
        $successLogs = ActivityLog::where('action', 'success')
            ->where('description', 'like', '%' . $transaksi->invoice . '%')
            ->first();
        $successTime = $successLogs?->created_at->format('Y-m-d H:i:s');
    
        $deliverLogs = ActivityLog::where('action', 'deliver')
            ->where('description', 'like', '%' . $transaksi->invoice . '%')
            ->first();
        $deliverTime = $deliverLogs?->created_at->format('Y-m-d H:i:s');
    
        $doneLogs = ActivityLog::where('action', 'done')
            ->where('description', 'like', '%' . $transaksi->invoice . '%')
            ->first();
        $doneTime = $doneLogs?->created_at->format('Y-m-d H:i:s');
    
        $cancelLogs = ActivityLog::where('action', 'cancel')
            ->where('description', 'like', '%' . $transaksi->invoice . '%')
            ->first();
        $cancelTime = $cancelLogs?->created_at->format('Y-m-d H:i:s');
    
        $status = $transaksi->status;
        $created = $transaksi->created_at->format('Y-m-d H:i:s');
    
        $isPaid = in_array($status, ['success', 'deliver', 'done', 'reviewed']);
        $isDelivered = in_array($status, ['deliver', 'done', 'reviewed']);
        $isDone = in_array($status, ['done', 'reviewed']);
        $isReviewed = $status === 'reviewed';
        $isCancelled = $status === 'cancel';
    @endphp
    
    <div class="lg:col-span-4 space-y-4">
        <x-card class="rounded-xl border border-base-300 shadow-sm" title="Status">
            <hr class="mb-6">
            <div class="px-4">
                {{-- Timeline 1: Pesanan dibuat --}}
                <x-timeline-item
                    title="Pesanan telah dilakukan"
                    icon="o-map-pin"
                    first
                    :pending="false"
                >
                    <x-slot name="subtitle">
                        {{ $created }}
                    </x-slot>
                    <x-slot name="description">
                        Kami telah menerima pesanan, menunggu konfirmasi pembayaran
                    </x-slot>
                </x-timeline-item>
    
                @if ($isCancelled)
                    {{-- Timeline 2: Dibatalkan --}}
                    <x-timeline-item title="Pesanan Dibatalkan" icon="o-x-circle" :pending="false" last>
                        <x-slot name="subtitle">
                            {{ $cancelTime }}
                        </x-slot>
                        <x-slot name="description">
                            Pesanan telah dibatalkan oleh sistem atau pengguna.
                        </x-slot>
                    </x-timeline-item>
                @else
                    {{-- Timeline 2: Pembayaran dikonfirmasi --}}
                    <x-timeline-item title="Pembayaran telah dikonfirmasi" icon="o-credit-card" :pending="!$isPaid">
                        <x-slot name="subtitle">
                            @if ($status === 'pending')
                                <a href="{{ url('/checkout/' . $transaksi->invoice) }}"
                                    class="text-blue-600 underline font-semibold">
                                    Lanjutkan ke Pembayaran
                                </a>
                            @else
                                {{ $isPaid ? $successTime : '-' }}
                            @endif
                        </x-slot>
                        <x-slot name="description">
                            Pembayaran telah dikonfirmasi, siap dibuat.
                        </x-slot>
                    </x-timeline-item>
    
                    {{-- Timeline 3: Pesanan selesai dibuat / dikirim --}}
                    <x-timeline-item title="Pesanan selesai dibuat" icon="o-truck" :pending="!$isDelivered">
                        <x-slot name="subtitle">
                            {{ $isDelivered ? $deliverTime : '-' }}
                        </x-slot>
                        <x-slot name="description">
                            Pesanan sedang diantar.
                            @if ($status === 'deliver')
                                <div class="mt-4">
                                    <x-button label="Pesanan telah sampai" icon="o-check-circle"
                                        wire:click="markAsDone" class="btn-success" />
                                </div>
                            @endif
                        </x-slot>
                    </x-timeline-item>
    
                    {{-- Timeline 4: Pesanan selesai --}}
                    <x-timeline-item title="Pesanan selesai" icon="o-check-badge" :pending="!$isDone" last>
                        <x-slot name="subtitle">
                            {{ $isDone ? $doneTime : '-' }}
                        </x-slot>
                        <x-slot name="description">
                            Pesanan telah selesai diterima pelanggan.
                            @if ($isDone && !$isReviewed)
                                <div class="flex flex-col w-32">
                                    <x-button spinner label="Ratings" @click="$wire.create()" icon="o-plus" />
                                </div>
                            @endif 
                        </x-slot>
                    </x-timeline-item>
                @endif
            </div>
        </x-card>
    </div>

    {{-- Modal Rating --}}
    <x-modal wire:model="createModal" title="Masukkan Rating">
        <div class="grid gap-4">
            @foreach ($this->transaksi->orders as $item)
                <div class="space-y-4">
                    <x-input label="Menu" value="{{ $item->menu->name }}" disabled />

                    <x-rating label="Rating" wire:model.live="newRating.{{ $item->menu->id }}" class="bg-yellow-400"
                        total="5" />

                    <x-textarea wire:model.live="newReview.{{ $item->menu->id }}" label="Review"
                        hint="Tulis ulasanmu di sini" />
                </div>
            @endforeach
        </div>

        <x-slot:actions>
            <div class="flex justify-end gap-4">
                <x-button label="Cancel" icon="o-x-mark" @click="$wire.createModal = false" class="btn-outline" />
                <x-button spinner label="Save" icon="o-check" class="btn-primary" wire:click="saveCreate" />
            </div>
        </x-slot:actions>
    </x-modal>
</div>
