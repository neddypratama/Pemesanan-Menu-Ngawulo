<?php

use App\Models\Cart;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    public array $cartItems = [];

    public function mount(): void
    {
        $this->loadCart();
    }

    public function loadCart(): void
    {
        if (Auth::check()) {
            $this->cartItems = Cart::with('menu')->where('user_id', Auth::id())->get()->toArray();
        }
    }

    public function incrementQty($id): void
    {
        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->first();
        if ($cart) {
            $cart->qty += 1;
            $cart->save();
            $this->loadCart();
            $this->success('Jumlah diperbaharui!', position: 'toast-buttom');
            logActivity('update', 'Menambahkan qty menu ' . $cart->menu->name . ' di cart');
        }
    }

    public function decrementQty($id): void
    {
        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->first();
        if ($cart && $cart->qty > 1) {
            $cart->qty -= 1;
            $cart->save();
            $this->loadCart();
            $this->success('Jumlah diperbaharui!', position: 'toast-buttom');
            logActivity('update', 'Mengurangi qty menu ' . $cart->menu->name . ' di cart');
        }
    }

    public function deleteCartItem($id): void
    {
        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->first();
        logActivity('delete', 'Menghapus ' . $cart->menu->name . ' dari cart');
        $cart->delete();
        $this->loadCart();
        $this->error('Dihapus dari keranjang!', position: 'toast-buttom');
    }

    public function clearCart(): void
    {
        $carts = Cart::where('user_id', Auth::id())->get();

        foreach ($carts as $cart) {
            logActivity('delete', 'Menghapus ' . $cart->menu->name . ' dari cart');
            $cart->delete();
        }

        $this->loadCart();
        $this->error('Keranjang dihapus!', position: 'toast-bottom');
    }

    protected $listeners = ['cartUpdated' => 'loadCart'];
};
?>

<x-dropdown class="relative">
    <x-slot:trigger>
        <x-button label="Cart" icon="fas.cart-shopping" class="btn-ghost btn-sm" responsive
            badge="{{ count($cartItems) }}" />
    </x-slot:trigger>

    <div class="w-80 px-4 py-3 space-y-3">
        @forelse ($cartItems as $item)
            <div class="flex items-start gap-3 border-b pb-3">
                <img src="{{ $item['menu']['photo'] }}" alt="{{ $item['menu']['name'] }}"
                    class="w-12 h-12 rounded object-cover" />
                <div class="flex-1">
                    <div class="text-sm font-semibold">{{ $item['menu']['name'] }}</div>
                    <div class="text-xs mb-1">Rp. {{ number_format($item['menu']['price'], 0, ',', '.') }}</div>
                </div>
                <div class="flex items-center text-xs">
                    <button wire:click.stop="decrementQty({{ $item['id'] }})"
                        class=" px-2 py-0.5 rounded">−</button>
                    <span class="text-sm font-semibold mx-2">{{ $item['qty'] }}</span>
                    <button wire:click.stop="incrementQty({{ $item['id'] }})"
                        class=" px-2 py-0.5 rounded">+</button>
                </div>
                <button wire:click="deleteCartItem({{ $item['id'] }})" class="text-pink-500 hover:text-pink-700"
                    title="Hapus item">
                    <x-icon name="fas.trash" class="w-4 h-4" />
                </button>
            </div>
        @empty
            <div class="text-center text-sm py-3">Keranjang kosong</div>
        @endforelse

        @if (count($cartItems))
            @php
                $totalQty = collect($cartItems)->sum('qty');
                $totalPrice = collect($cartItems)->sum(fn($item) => $item['menu']['price'] * $item['qty']);
            @endphp

            <div class="flex justify-between text-sm font-medium border-t pt-3">
                <div>{{ $totalQty }} item(s)</div>
                <div>Rp. {{ number_format($totalPrice, 0, ',', '.') }}</div>
            </div>

            <div class="flex justify-between mt-3 gap-2">
                <button wire:click="clearCart" class="text-red-500 text-sm flex items-center gap-1 hover:text-red-700">
                    <x-icon name="fas.trash" class="w-4 h-4" />
                    Trash cart
                </button>

                <x-button link="/cart" class="btn-primary ml-auto flex items-center gap-2" size="sm"
                    icon="fas.arrow-right">
                    Go to cart
                </x-button>
            </div>
        @endif
    </div>
</x-dropdown>
