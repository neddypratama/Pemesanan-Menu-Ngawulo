<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Menu;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

new #[Layout('components.layouts.buy')] class extends Component {
    use Toast;

    public Menu $menu;
    public float $averageRating = 0;
    public int $totalRatings = 0;
    public bool $isInCart = false;

    public string $guestName = '';
    public bool $showGuestNameModal = false;

    public function mount(Menu $menu): void
    {
        $this->menu = $menu->load(['ratings', 'kategori']);
        $this->averageRating = round($this->menu->ratings->avg('rating'), 1);
        $this->totalRatings = $this->menu->ratings->count();

        if (session()->has('guest_name')) {
            $this->guestName = session('guest_name');
        }

        $this->checkCartStatus();
    }

    public function checkCartStatus(): void
    {
        if (Auth::check()) {
            $this->isInCart = Cart::where('user_id', Auth::id())->where('menu_id', $this->menu->id)->exists();
        } else {
            $sessionId = session()->getId();
            $this->isInCart = Cart::where('session_id', $sessionId)->where('menu_id', $this->menu->id)->exists();
        }
    }

    public function addToCart(): void
    {
        if (Auth::check()) {
            $cartItem = Cart::where('user_id', Auth::id())->where('menu_id', $this->menu->id)->first();

            if ($cartItem) {
                $cartItem->increment('qty');
            } else {
                Cart::create([
                    'user_id' => Auth::id(),
                    'menu_id' => $this->menu->id,
                    'qty' => 1,
                ]);
            }

            logActivity('create', 'Menambahkan menu ' . $this->menu->name . ' ke cart');
            $this->toast('success', 'Produk berhasil ditambahkan ke keranjang!');
        } else {
            if (!$this->guestName) {
                // Jika guestName kosong, buka modal input nama
                $this->showGuestNameModal = true;
                return;
            }

            // Simpan guestName ke session
            session(['guest_name' => $this->guestName]);

            $sessionId = session()->getId();
            $cartItem = Cart::where('session_id', $sessionId)->where('menu_id', $this->menu->id)->first();

            if ($cartItem) {
                $cartItem->increment('qty');
            } else {
                Cart::create([
                    'session_id' => $sessionId,
                    'menu_id' => $this->menu->id,
                    'qty' => 1,
                    'guest_name' => $this->guestName,
                ]);
            }
            $this->showGuestNameModal = false;
            $this->toast('success', 'Produk berhasil ditambahkan ke keranjang!');
        }

        $this->checkCartStatus();
        $this->dispatch('cartUpdated');
    }

    public function removeFromCart(): void
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->where('menu_id', $this->menu->id)->delete();

            logActivity('delete', 'Menghapus menu ' . $this->menu->name . ' dari cart');
        } else {
            $sessionId = session()->getId();

            Cart::where('session_id', $sessionId)->where('menu_id', $this->menu->id)->delete();
        }

        $this->checkCartStatus();
        $this->dispatch('cartUpdated');
        $this->toast('info', 'Produk dihapus dari keranjang.');
    }
};
?>

<div class="max-w-6xl mx-auto px-4 py-10">
    {{-- Toast sudah otomatis tampil jika ada pesan --}}

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 items-start" wire:poll="checkCartStatus">

        {{-- Gambar Produk --}}
        <div class="lg:col-span-4 flex justify-center md:justify-start">
            <img src="{{ $menu->photo }}" alt="{{ $menu->name }}" class="w-60 h-60 object-cover rounded-lg shadow-md" />
        </div>

        {{-- Detail Produk --}}
        <div class="lg:col-span-6 space-y-5">
            <h2 class="text-2xl md:text-3xl font-bold">{{ $menu->name }}</h2>

            <div class="text-lg font-semibold">
                Rp. {{ number_format($menu->price, 0, ',', '.') }}
            </div>

            {{-- Add / Remove from Cart --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                @if ($isInCart)
                    <x-button spinner wire:click="removeFromCart"
                        class="flex items-center text-sm rounded btn-error btn-sm" icon="fas.trash">
                        Hapus dari keranjang
                    </x-button>
                @else
                    <x-button spinner wire:click="addToCart"
                        class="flex items-center text-sm rounded btn-sm btn-primary" icon="fas.shopping-cart">
                        Tambah ke keranjang
                    </x-button>
                @endif

                {{-- Rating --}}
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

            {{-- Deskripsi --}}
            <p class="text-sm md:text-base leading-relaxed text-gray-700">
                {{ $menu->deskripsi }}
            </p>
        </div>

        {{-- Tombol Kembali --}}
        <div class="lg:col-span-2 flex justify-end">
            <x-button label="Kembali" link="/" class="btn-gost" icon="fas.arrow-left" />
        </div>
    </div>
    {{-- Modal Mary UI untuk input nama guest --}}
    <x-modal wire:model="showGuestNameModal" title="Mohon Isi Nama Anda" max-width="md" class="backdrop-blur"
        x-on:keydown.escape.window="$wire.showGuestNameModal = false">

        <div class="space-y-4">
            <input wire:model.defer="guestName" type="text" placeholder="Masukkan nama Anda"
                class="w-full border border-gray-300 rounded-md p-2" />
            @error('guestName')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.showGuestNameModal = false" class="btn-secondary" />
            <x-button label="Simpan & Tambah" wire:click="addToCart" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
