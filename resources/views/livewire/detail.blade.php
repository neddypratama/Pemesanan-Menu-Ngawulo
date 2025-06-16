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
            $this->isInCart = Cart::where('user_id', $user->id)->where('menu_id', $this->menu->id)->exists();
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

        $cartItem = Cart::where('user_id', $user->id)->where('menu_id', $this->menu->id)->first();

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
        // $this->success('Keranjang diperbaharui!', position: 'toast-buttom');
        session()->flash('status', 'Produk berhasil ditambahkan ke keranjang!');
        logActivity('create', 'Menambahkan menu ' . $this->menu->name .' ke cart' );

    }

    public function removeFromCart(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        Cart::where('user_id', $user->id)->where('menu_id', $this->menu->id)->delete();

        $this->checkCartStatus();
        $this->dispatch('cartUpdated');
        // $this->success('Keranjang diperbaharui!', position: 'toast-buttom');
        session()->flash('status', 'Produk dihapus dari keranjang.');
        logActivity('delete', 'Menghapus menu ' . $this->menu->name .' ke cart' );
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-10">
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
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 items-start" wire:poll="checkCartStatus">
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
                    <x-button spinner wire:click="removeFromCart" class="flex items-center text-sm rounded btn-error btn-sm"
                        icon="fas.trash">
                        Hapus dari keranjang
                    </x-button>
                @else
                    <x-button spinner wire:click="addToCart" class="flex items-center text-sm rounded btn-sm btn-primary"
                        icon="fas.shopping-cart">
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
            <x-button label="Kembali" link="/" class="btn-gost" icon="fas.arrow-left" />
        </div>
    </div>
</div>
