<?php

use App\Models\Cart;
use App\Models\Transaksi;
use App\Models\Order;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;

new #[Layout('components.layouts.buy')] class extends Component {
    use Toast;
    public $cart = [];

    #[Rule('required|unique:transaksis')]
    public string $invoice = '';

    public bool $showCatatanModal = false;
    public string $catatan = '';
    public int|null $selectedCartItemId = null;

    public function openCatatanModal($cartItemId)
    {
        $this->selectedCartItemId = $cartItemId;
        $this->catatan = Cart::find($cartItemId)?->keterangan ?? '';
        $this->showCatatanModal = true;
    }

    public function simpanCatatan()
    {
        $cart = Cart::where('id', $this->selectedCartItemId);
        $cart->update([
            'keterangan' => $this->catatan,
        ]);
        logActivity('update', 'Menambahkan keterangan untuk ' . $cart->menu->name );

        $this->showCatatanModal = false;
        $this->fetchCart();
        $this->success('Catatan diperbaharui!', position: 'toast-buttom');
    }

    protected $listeners = ['cartUpdated' => 'fetchCart'];

    public function mount()
    {
        $this->fetchCart();
    }

    public function fetchCart()
    {
        $this->cart = Cart::with('menu')->where('user_id', Auth::id())->get()->toArray();
    }

    public function incrementQty($id)
    {
        $cart = Cart::find($id);
        $cart->increment('qty');
        $this->fetchCart();
        $this->dispatch('cartUpdated');
        $this->success('Jumlah diperbaharui!', position: 'toast-buttom');
        logActivity('update', 'Menambahkan qty menu ' . $cart->menu->name .' di cart' );
    }

    public function decrementQty($id)
    {
        $cart = Cart::find($id);
        if ($cart->qty > 1) {
            $cart->decrement('qty');
        }
        $this->fetchCart();
        $this->dispatch('cartUpdated');
        $this->success('Jumlah diperbaharui!', position: 'toast-buttom');
        logActivity('update', 'Mengurangi qty menu ' . $cart->menu->name .' di cart' );
    }

    public function removeItem($id)
    {
        $cart = Cart::find($id);
        logActivity('delete', 'Menghapus ' . $cart->menu->name . ' dari cart' );
        $cart->delete();
        $this->fetchCart();
        $this->dispatch('cartUpdated');
        $this->error('Dihapus dari keranjang!', position: 'toast-buttom');
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['menu']['price'] * $item['qty']);
    }

    public function goToCheckout()
    {
        if (empty($this->cart)) {
            return; // Tidak ada item, tidak lanjut
        }

        $userId = Auth::id();

        $value = now();
        $tanggal = \Carbon\Carbon::parse($value)->format('Ymd');
        $count = Transaksi::whereDate('created_at', \Carbon\Carbon::parse($value)->toDateString())->count() + 1;
        $this->invoice = 'INV-' . $tanggal . '-' .Str::upper(Str::random(10));

        // dd($this->invoice);
        // 1. Buat transaksi utama
        $transaction = Transaksi::create([
            'invoice' => $this->invoice,
            'tanggal' => now()->format('Y-m-d\TH:i'),
            'user_id' => $userId,
            'total' => $this->total,
            'status' => 'new', // atau sesuai kebutuhan
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        logActivity('create', 'Membuat transaksi baru dengan invoice ' . $this->invoice );

        // 2. Masukkan item cart ke order detail
        foreach ($this->cart as $item) {
            $order = Order::create([
                'transaksi_id' => $transaction->id,
                'menu_id' => $item['menu_id'],
                'qty' => $item['qty'],
                'keterangan' => $item['keterangan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            logActivity('create', 'Membuat order dengan menu ' . $order->menu->name);
        }

        // 3. Hapus semua cart
        $cart = Cart::where('user_id', $userId);
        logActivity('delete', 'Menghapus cart oleh user ' . auth()->user()->name );
        $cart->delete();

        // 4. Perbarui cart di frontend
        $this->fetchCart();
        $this->dispatch('cartUpdated');

        // 5. Redirect ke halaman checkout dengan ID transaksi
        return redirect("/checkout/{$transaction->invoice}");
    }

    public function goToHome()
    {
        return redirect('/');
    }
};
?>

<div class="mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 rounded">

        {{-- Gambar ilustrasi --}}
        <div class="lg:col-span-3 flex justify-center mb-6 lg:mb-0">
            <img src="https://orange.mary-ui.com/images/cart.png" class="w-40 sm:w-60 md:w-72 lg:w-80" />
        </div>

        {{-- Daftar cart --}}
        <div class="lg:col-span-5">
            <x-card class="p-4 sm:p-6 rounded-2xl shadow" wire:poll="fetchCart">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold mb-4">Keranjang</h2>
                    <x-button wire:click="goToHome" class="btn-gost mb-2" icon-right="fas.arrow-right">
                        Cari menu
                    </x-button>
                </div>
                <hr class="mb-5">

                @forelse ($cart as $item)
                    <div class="flex sm:flex-row justify-between sm:items-center gap-3 mb-6">
                        <div class="flex items-center gap-3 sm:gap-5">
                            <img src="{{ $item['menu']['photo'] }}" class="w-12 h-12 rounded" />
                            <div>
                                <p class="font-semibold">{{ $item['menu']['name'] }}</p>
                                <p class="text-sm font-thin">Rp.
                                    {{ number_format($item['menu']['price'], 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="flex sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                            <x-button class="btn-sm size-0" icon="o-pencil"
                                wire:click="openCatatanModal({{ $item['id'] }})" tooltip="Catatan" responsive />
                            <div class="flex items-center">
                                <x-button wire:click="decrementQty({{ $item['id'] }})" class="btn-sm rounded size-0"
                                    icon="o-minus" responsive />
                                <span class="mx-2 font-bold">{{ $item['qty'] }}</span>
                                <x-button wire:click="incrementQty({{ $item['id'] }})" class="btn-sm rounded size-0"
                                    icon="o-plus" responsive />
                                <x-button wire:click="removeItem({{ $item['id'] }})"
                                    class="btn-sm btn-error ml-2 size-0" icon="o-trash" responsive />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm py-3">Keranjang kosong</div>
                @endforelse
                <hr class="my-5">
                <div class="flex justify-between">
                    <div>Total</div>
                    <div class="font-bold text-lg">Rp. {{ number_format($this->total, 0, ',', '.') }}</div>
                </div>
            </x-card>
        </div>

        {{-- Tombol checkout --}}
        <div class="lg:col-span-4">
            <x-card class="p-4 sm:p-6 rounded-2xl shadow flex flex-col justify-between ">
                <div>
                    <h2 class="text-lg font-bold mb-4">I am done</h2>
                    <hr class="my-5">
                    <p class="text-sm">Pastikan pesananmu sudah benar sebelum checkout.</p>
                </div>
                <x-button wire:click="goToCheckout" class="btn-primary w-full mt-6 py-2 justify-center">
                    Checkout
                </x-button>
            </x-card>
        </div>
    </div>

    {{-- Modal Catatan --}}
    <x-modal wire:model="showCatatanModal" title="Catatan Pesanan"
        subtitle="Tambahkan permintaan khusus untuk item ini">
        <x-form no-separator>
            <x-textarea label="Catatan" placeholder="Contoh: tanpa sambal, saus dipisah..." wire:model.live="catatan" />

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.showCatatanModal = false" />
                <x-button label="Simpan" class="btn-primary" wire:click="simpanCatatan" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
