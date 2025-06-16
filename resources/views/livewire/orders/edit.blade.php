<?php

use Livewire\Volt\Component;
use App\Models\Transaksi;
use App\Models\Order;
use App\Models\Menu;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

new class extends Component {
    use Toast, WithFileUploads;

    public Transaksi $transaksi;

    #[Rule('required')]
    public string $invoice = '';

    #[Rule('required|integer|min:1')]
    public int $total = 0;

    #[Rule('required|sometimes')]
    public ?int $user_id = null;

    #[Rule('required|array|min:1')]
    public array $orders = [];

    public ?string $tanggal = null;

    public function with(): array
    {
        return [
            'users' => User::where('role_id', 4)->get(),
            'menus' => Menu::all(),
        ];
    }

    public function mount($id): void
    {
        $this->transaksi = Transaksi::findOrFail($id);

        $this->invoice = $this->transaksi->invoice ?? '';
        $this->total = $this->transaksi->total ?? 0;
        $this->user_id = $this->transaksi->user_id;
        $this->tanggal =  \Carbon\Carbon::parse($this->transaksi->tanggal)->format('Y-m-d\TH:i:s');

        // Load orders
        $this->orders = $this->transaksi->orders
            ->map(function ($order) {
                return [
                    'menu_id' => $order->menu_id,
                    'qty' => $order->qty,
                    'price' => $order->menu->price ?? 0,
                    'keterangan' => $order->keterangan ?? null,
                ];
            })
            ->toArray();
    }

    public function updatedTanggal($value): void
    {
        if ($value) {
            $tanggal = \Carbon\Carbon::parse($value)->format('Ymd');
            $count = Transaksi::whereDate('created_at', \Carbon\Carbon::parse($value)->toDateString())->count();
            $this->invoice = 'INV-' . $tanggal . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }
    }

    public function calculateTotal(): void
    {
        $this->total = collect($this->orders)->sum(fn($order) => ($order['price'] ?? 0) * ($order['qty'] ?? 1));
    }

    public function save(): void
    {
        $data = $this->validate();

        // Update transaksi
        $this->transaksi->update([
            'invoice' => $this->invoice,
            'total' => $this->total,
            'user_id' => $this->user_id,
        ]);
        logActivity('updated', 'Merubah data transaksi ' . $this->transaksi->invoice);

        // Hapus order lama dan tambahkan yang baru
        $this->transaksi->orders()->delete();
        foreach ($this->orders as $order) {
            $order = Order::create([
                'transaksi_id' => $this->transaksi->id,
                'menu_id' => $order['menu_id'],
                'qty' => $order['qty'],
                'keterangan' => $order['keterangan'],
            ]);
            logActivity('updated', 'Merubah data order dari ' . $order->id);
        }

        // Notifikasi sukses
        $this->success('Transaksi berhasil diperbarui!', redirectTo: '/orders');
    }

    public function addOrder(): void
    {
        $this->orders[] = ['menu_id' => null, 'qty' => 1, 'price' => 0, 'keterangan' => null];
    }

    public function removeOrder(int $index): void
    {
        unset($this->orders[$index]);
        $this->orders = array_values($this->orders);
        $this->calculateTotal();
    }

    public function updatedOrders()
    {
        $this->calculateTotal();
    }
};
?>

<div class="p-4 space-y-6">
    <x-header title="Edit {{ $this->invoice }}" separator progress-indicator />

    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic Transaction" subtitle="Edit transaction details" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-input label="Invoice" wire:model.live="invoice" readonly />
                <x-select label="User" wire:model="user_id" :options="$users" placeholder="---" />
                <x-datetime label="Date + Time" wire:model.live="tanggal" icon="o-calendar" type="datetime-local" step="60" />
            </div>
        </div>

        <hr class="my-5" />
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Order Items" subtitle="Edit menu items in transaction" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                @foreach ($orders as $index => $order)
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <x-select wire:model.live="orders.{{ $index }}.menu_id" label="Menu" :options="$menus"
                            placeholder="Pilih Menu" />
                        <x-input wire:model.live="orders.{{ $index }}.price" label="Price" type="number" readonly />
                    </div>
                    <x-input wire:model.live="orders.{{ $index }}.qty" label="Qty" type="number" min="1" />
                    <x-input wire:model.live="orders.{{ $index }}.keterangan" label="Keterangan"
                        hint="Tambahkan catatan" />
                    <x-button spinner icon="o-trash" class="bg-red-500 text-white"
                        wire:click="removeOrder({{ $index }})" />
                @endforeach
                <x-button spinner icon="o-plus" label="Tambah Item" wire:click="addOrder" class="mt-3" />
                <x-input label="Total" wire:model.live="total" prefix="Rp" money="IDR" readonly/>
            </div>
        </div>

        <x-slot:actions>
            <x-button spinner label="Cancel" link="/orders" />
            <x-button spinner label="Update" icon="o-pencil" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>

    </x-form>
</div>
