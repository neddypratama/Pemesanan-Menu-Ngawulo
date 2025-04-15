<?php

use App\Models\Transaksi;
use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Midtrans\Snap;
use Midtrans\Config;

new #[Layout('components.layouts.buy')] class extends Component {
    public $transaksi;
    public $orders = [];

    public string $snapToken = '';

    public function generateSnapToken()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . now()->format('Y-m-d\TH:i'),
                'gross_amount' => $this->transaksi->total,
            ],
            'customer_details' => [
                'first_name' => $this->transaksi->user->name,
                'email' => $this->transaksi->user->email,
            ],
        ];

        $this->snapToken = Snap::getSnapToken($params);
    }

    public bool $showCatatanModal = false;
    public string $catatan = '';
    public int|null $selectedCartItemId = null;

    public function openCatatanModal($cartItemId)
    {
        $this->selectedCartItemId = $cartItemId;
        $this->catatan = Order::find($cartItemId)?->keterangan ?? '';
        $this->showCatatanModal = true;
    }

    public function mount($invoice)
    {
        // menerima invoice dari URL
        $this->transaksi = Transaksi::with(['orders.menu', 'user'])->findOrFail($invoice);
        $this->orders = $this->transaksi->orders->toArray();

        $this->generateSnapToken();
    }

    public function getTotalProperty()
    {
        return $this->transaksi ? $this->transaksi->total : 0;
    }
};
?>

<div class="mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10  rounded">
        <div class="lg:col-span-3 flex items-center justify-center">
            <img src="https://orange.mary-ui.com/images/checkout.png" class="w-80" />
        </div>

        <div class="lg:col-span-5">
            <x-card class=" p-6 rounded-2xl shadow ">
                <h2 class="text-lg font-bold mb-4">Checkout</h2>
                <hr class="mb-5">

                @foreach ($orders as $order)
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center">
                            <img src="{{ $order['menu']['photo'] }}" class="w-12 h-12 rounded" />
                            <div>
                                <p class="font-semibold ml-5">{{ $order['menu']['name'] }}</p>
                                <p class="text-sm ml-5 font-thin">Rp.
                                    {{ number_format($order['menu']['price'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <x-button class="btn-sm" icon="o-pencil" wire:click="openCatatanModal({{ $order['id'] }})"
                                label="Catatan" responsive />
                            <span class="ml-5 font-bold">{{ $order['qty'] }}</span>
                        </div>
                    </div>
                @endforeach

                <hr class="my-5">
                <div class="flex justify-between">
                    <div>Total</div>
                    <div class="font-bold text-lg">Rp. {{ number_format($this->total, 0, ',', '.') }}</div>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-4">
            <x-card class=" p-6 rounded-2xl shadow flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold mb-4">Pembayaran</h2>
                    <hr class="my-5">
                    <p class="text-sm">Silahkan melakukan pembayaran.</p>
                </div>
                <x-button class="btn-primary w-full mt-6 py-2 justify-center" onclick="payNow('{{ $snapToken }}')">
                    Bayar Sekarang
                </x-button>
            </x-card>
        </div>

        {{-- Modal Catatan --}}
        <x-modal wire:model="showCatatanModal" title="Catatan Pesanan">
            <x-form no-separator>
                <x-textarea label="Catatan" readonly wire:model.live="catatan" />
            </x-form>
        </x-modal>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
    function payNow(token) {
        window.snap.pay(token, {
            onSuccess: function(result) {
                console.log("success", result);
                // Kirim ke backend kalau mau simpan hasil transaksi
            },
            onPending: function(result) {
                console.log("pending", result);
            },
            onError: function(result) {
                console.log("error", result);
            },
            onClose: function() {
                alert("Kamu menutup tanpa menyelesaikan pembayaran.");
            }
        });
    }
</script>
