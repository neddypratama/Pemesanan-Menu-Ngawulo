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

    #[Rule('required|unique:transaksis')]
    public string $invoice = '';

    #[Rule('required|integer|min:1')]
    public int $total = 0;

    #[Rule('required|sometimes')]
    public ?int $user_id = null;

    #[Rule('sometimes')]
    public ?string $keterangan = null;

    #[Rule('required|array|min:1')]
    public array $orders = [];

    public ?string $tanggal = null;

    public function with(): array
    {
        return [
            'users' => User::all(),
            'menus' => Menu::all(),
        ];
    }

    public function updatedTanggal($value): void
    {
        if ($value) {
            $tanggal = \Carbon\Carbon::parse($value)->format('Ymd');
            $count = Transaksi::whereDate('created_at', \Carbon\Carbon::parse($value)->toDateString())->count() + 1;
            $this->invoice = 'INV-' . $tanggal . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
    }

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d\TH:i');
        $this->updatedTanggal($this->tanggal);
    }

    public function updatedOrders($value, $key)
    {
        if (str_ends_with($key, '.menu_id')) {
            $index = explode('.', $key)[0];
            $menuId = data_get($this->orders, "$index.menu_id", null);
            $menu = Menu::find($menuId);
            $this->orders[$index]['price'] = $menu ? $menu->price : 0;
        }

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->total = collect($this->orders)->sum(function ($order) {
            $menu = Menu::find(data_get($order, 'menu_id', null));
            return $menu ? $menu->price * data_get($order, 'qty', 0) : 0;
        });
    }

    public function save(): void
    {
        // Pastikan invoice selalu terisi
        if (empty($this->invoice)) {
            $this->updatedTanggal($this->tanggal);
        }
        $data = $this->validate();

        $transaksi = Transaksi::create([
            'invoice' => $this->invoice,
            'total' => $this->total,
            'user_id' => $this->user_id,
            'tanggal' => $this->tanggal,
            'keterangan' => $this->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($this->orders as $order) {
            Order::create([
                'transaksi_id' => $transaksi->id,
                'menu_id' => $order['menu_id'],
                'qty' => $order['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->success('Transaksi dan Order berhasil dibuat!', redirectTo: '/orders');
    }

    public function addOrder(): void
    {
        $this->orders[] = [
            'menu_id' => null,
            'price' => 0,
            'qty' => 1,
        ];
    }

    public function removeOrder(int $index): void
    {
        unset($this->orders[$index]);
        $this->orders = array_values($this->orders);
        $this->calculateTotal();
    }
};

?>

<div class="p-4 space-y-6">
    <x-header title="Create Transaction" separator progress-indicator />

    <x-form wire:submit="save">
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Basic Transaction" subtitle="Basic info from transaction" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-input label="Invoice" wire:model.live="invoice" readonly />
                <x-select label="User" wire:model="user_id" :options="$users" placeholder="---" />
                <x-datetime label="Date + Time" wire:model.live="tanggal" icon="o-calendar" type="datetime-local" />
            </div>
        </div>

        <hr class="my-5" />
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Order Items" subtitle="Tambah menu ke dalam transaksi" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                @foreach ($orders as $index => $order)
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <x-select wire:model.live="orders.{{ $index }}.menu_id" label="Menu" :options="$menus"
                            placeholder="Pilih Menu" />
                        <x-input label="Price" wire:model.live="orders.{{ $index }}.price" prefix="Rp"
                            money="IDR" readonly />
                    </div>
                    <x-input wire:model.live="orders.{{ $index }}.qty" label="Qty" type="number"
                        min="1" />
                    <x-button icon="o-trash" class="bg-red-500 text-white"
                        wire:click="removeOrder({{ $index }})" />
                @endforeach

                <x-button icon="o-plus" label="Tambah Item" wire:click="addOrder" class="mt-3" />
                <x-input label="Total" wire:model.live="total" prefix="Rp" money="IDR" readonly />
            </div>
        </div>

        <hr class="my-5" />
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="Note Transaction" subtitle="Note about the menu" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-editor wire:model="keterangan" label="Keterangan" hint="Tambahkan catatan transaksi" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="/orders" />
            <x-button label="Create" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
