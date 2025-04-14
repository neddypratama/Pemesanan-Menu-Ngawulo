<?php

use Livewire\Volt\Component;
use App\Models\Menu;
use Livewire\Attributes\Layout;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

new #[Layout('components.layouts.buy')] class extends Component {
    use Toast;

    public Menu $menu;
    public float $averageRating = 0;
    public int $totalRatings = 0;
    public bool $isInCart = false;

    public function mount(Menu $menu): void
    {
        $this->menu = $menu->load(['ratings', 'kategori']);
        $this->averageRating = round($menu->ratings->avg('rating'), 1);
        $this->totalRatings = $menu->ratings->count();

        $this->checkCartStatus();
    }

    public function checkCartStatus(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->isInCart = Cart::where('user_id', $user->id)
                ->where('menu_id', $this->menu->id)
                ->exists();
        } else {
            $this->isInCart = false;
        }
    }

    public function addToCart(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $cartItem = Cart::where('user_id', $user->id)
            ->where('menu_id', $this->menu->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('qty');
        } else {
            Cart::create([
                'user_id' => $user->id,
                'menu_id' => $this->menu->id,
                'qty' => 1,
            ]);
        }

        $this->checkCartStatus();
        $this->dispatch('cartUpdated');
        $this->success('Keranjang diperbaharui!', position: 'toast-buttom');
        // session()->flash('status', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function removeFromCart(): void
    {
        $user = Auth::user();
        if (!$user) return;

        Cart::where('user_id', $user->id)
            ->where('menu_id', $this->menu->id)
            ->delete();

        $this->checkCartStatus();
        $this->dispatch('cartUpdated');
        $this->success('Keranjang diperbaharui!', position: 'toast-buttom');
        // session()->flash('status', 'Produk dihapus dari keranjang.');
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 items-start">
        <!-- Gambar Produk -->
        <div class="lg:col-span-4 flex justify-center md:justify-start">
            <img src="{{ $menu->photo }}" alt="{{ $menu->name }}"
                class="w-60 h-60 object-cover rounded-lg shadow-md" />
        </div>

        <!-- Detail Produk -->
        <div class="lg:col-span-6 space-y-5">
            <h2 class="text-2xl md:text-3xl font-bold">{{ $menu->name }}</h2>

            <div class="text-lg font-semibold ">
                Rp. {{ number_format($menu->price, 0, ',', '.') }}
            </div>

            <!-- Add / Remove from Cart -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                @if ($isInCart)
                    <x-button wire:click="removeFromCart"
                        class="flex items-center text-sm rounded text-red-500 hover:text-red-700 btn-sm">
                        <x-icon name="fas.trash" class="w-4 h-4" />
                        Hapus dari keranjang
                    </x-button>
                @else
                    <x-button wire:click="addToCart"
                        class="flex items-center text-sm rounded btn-sm">
                        <x-icon name="fas.shopping-cart" class="w-4 h-4" />
                        Tambah ke keranjang
                    </x-button>
                @endif

                <!-- Rating -->
                <div class="flex items-center">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= floor($averageRating))
                            <x-icon name="fas.star" class="text-yellow-400 w-4 h-4" />
                        @else
                            <x-icon name="fas.star" class="text-gray-300 w-4 h-4" />
                        @endif
                    @endfor
                    <span class="text-sm ml-1">({{ $totalRatings }})</span>
                </div>
            </div>

            <!-- Deskripsi -->
            <p class="text-sm md:text-base leading-relaxed text-gray-700">
                {{ $menu->deskripsi }}
            </p>
        </div>

        <!-- Tombol Kembali -->
        <div class="lg:col-span-2 flex justify-end">
            <x-button label="Kembali" link="/" class="btn-ghost" icon="fas.arrow-left" />
        </div>
    </div>
</div>
