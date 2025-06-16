<?php

use App\Models\Transaksi;
use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;
use Illuminate\Support\Str;

new #[Layout('components.layouts.buy')] class extends Component {
    public $transaksi;
    public $orders = [];
    public string $snapToken = '';
    public bool $showCatatanModal = false;
    public string $catatan = '';
    public int|null $selectedCartItemId = null;

    public function mount($invoice)
    {
        $this->transaksi = Transaksi::with(['orders.menu', 'user'])
            ->where('invoice', $invoice)
            ->firstOrFail();

        if ($this->transaksi->status === 'success') {
            return redirect()->route('orders.show', $this->transaksi->id)->with('status', 'Pembayaran telah dilakukan.');
        }

        $this->orders = $this->transaksi->orders->toArray();

        if ($this->transaksi->token && $this->transaksi->midtrans_id) {
            try {
                $this->configureMidtrans();
                $status = Transaction::status($this->transaksi->midtrans_id);

                if (in_array($status->transaction_status, ['pending', 'capture', 'authorize'])) {
                    $this->snapToken = $this->transaksi->token;
                    $this->payNow($this->snapToken);
                } elseif (in_array($status->transaction_status, ['expire', 'cancel', 'deny'])) {
                    if ($status->transaction_status === 'cancel') {
                        $this->transaksi->update(['status' => 'cancel']);
                        logActivity('cancel', 'Merubah status cancel transaksi ' . $this->transaksi->invoice);
                    } else {
                        $this->transaksi->update(['status' => 'error']);
                        logActivity('error', 'Merubah status error transaksi ' . $this->transaksi->invoice);
                    }
                    $this->generateSnapToken(true);
                } elseif ($status->transaction_status === 'settlement') {
                    $this->transaksi->update(['status' => 'success']);
                    logActivity('success', 'Merubah status success transaksi ' . $this->transaksi->invoice);
                    return redirect()->route('orders.show', $this->transaksi->id)->with('status', 'Pembayaran telah dilakukan.');
                }
            } catch (\Exception $e) {
                $this->generateSnapToken(true);
            }
        } else {
            $this->generateSnapToken();
        }
    }

    public function configureMidtrans()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function generateSnapToken($forceNewOrderId = false)
    {
        $this->configureMidtrans();

        $orderId = $forceNewOrderId ? 'ORDER-' . now()->format('YmdHis') . '-' . Str::random(4) : ($this->transaksi->midtrans_id ?: 'ORDER-' . now()->format('YmdHis') . '-' . Str::random(4));

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $this->transaksi->total,
            ],
            'customer_details' => [
                'first_name' => $this->transaksi->user->name,
                'email' => $this->transaksi->user->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $this->transaksi->update([
                'snap_token' => $snapToken,
                'midtrans_id' => $orderId,
                'status' => 'pending',
            ]);
            logActivity('update', 'Menambah token dan midtrans id transaksi ' . $this->transaksi->invoice);
            logActivity('pending', 'Merubah status pending transaksi ' . $this->transaksi->invoice);

            $this->snapToken = $snapToken;
        } catch (\Exception $e) {
            $this->generateSnapToken(true);
        }
    }

    public function openCatatanModal($cartItemId)
    {
        $this->selectedCartItemId = $cartItemId;
        $this->catatan = Order::find($cartItemId)?->keterangan ?? '';
        $this->showCatatanModal = true;
    }

    public function getTotalProperty()
    {
        return $this->transaksi ? $this->transaksi->total : 0;
    }

    public function updatePaymentStatus(array $result)
    {
        $orderId = $result['order_id'] ?? null;
        $status = $result['transaction_status'] ?? null;

        if ($orderId && $status) {
            $transaksi = Transaksi::where('midtrans_id', $orderId)->first();

            if ($transaksi) {
                if ($status == 'settlement') {
                    $transaksi->update(['status' => 'success']);
                    logActivity('success', 'Merubah status success transaksi ' . $transaksi->invoice);
                } elseif ($status == 'deny' || $status == 'expire') {
                    $transaksi->update(['status' => 'error']);
                    logActivity('error', 'Merubah status error transaksi ' . $transaksi->invoice);
                } elseif ($status == 'cancel') {
                    $transaksi->update(['status' => 'cancel']);
                    logActivity('cancel', 'Merubah status cancel transaksi ' . $transaksi->invoice);
                }

                $this->transaksi = $transaksi;
            }
        }

        return redirect()->route('orders.show', $this->transaksi->id);
    }

    public function payNow($token)
    {
        echo "<script src='https://app.sandbox.midtrans.com/snap/snap.js' data-client-key='" . config('midtrans.client_key') . "'></script>";
        echo "<script> window.snap.pay('$token'); </script>";
    }
};
?>

<div class="mx-auto" wire:id="{{ $this->getId() }}">
    @if (session('status') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
            class="fixed top-5 right-5 z-50 max-w-xs w-full p-4 rounded-lg shadow text-sm text-white transition duration-300"
            :class="{
                'bg-green-500': '{{ session('status') }}',
                'bg-red-500': '{{ session('error') }}'
            }"
            x-transition:enter="transition ease-out duration-300" x-transition:leave="transition ease-in duration-300">
            <div class="flex justify-between items-center">
                <span class="flex-1">
                    {{ session('status') ?? session('error') }}
                </span>
                <button @click="show = false" class="ml-2">&times;</button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <div class="lg:col-span-3 flex items-center justify-center">
            <img src="https://orange.mary-ui.com/images/checkout.png" class="w-80" />
        </div>

        <div class="lg:col-span-5">
            <x-card class="p-6 rounded-2xl shadow">
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
                            <x-button spinner class="btn-sm" icon="o-pencil" wire:click="openCatatanModal({{ $order['id'] }})"
                                label="Catatan" />
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
            <x-card class="p-6 rounded-2xl shadow flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold mb-4">Pembayaran</h2>
                    <hr class="my-5">
                    <p class="text-sm">Silahkan melakukan pembayaran.</p>
                </div>
                <x-button spinner class="btn-primary w-full mt-6 py-2 justify-center" onclick="payNow('{{ $snapToken }}')">
                    Bayar Sekarang
                </x-button>
            </x-card>
        </div>

        <x-modal wire:model="showCatatanModal" title="Catatan Pesanan">
            <x-form no-separator>
                <x-textarea label="Catatan" readonly wire:model.live="catatan" />
            </x-form>
        </x-modal>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}">
</script>
<script>
    function payNow(token) {
        window.snap.pay(token, {
            onSuccess: function(result) {
                const component = Livewire.find('{{ $this->getId() }}');
                component.call('updatePaymentStatus', result);
            },
            onPending: function(result) {
                alert("Pembayaran masih dalam proses, silakan cek di halaman My Order.");
            },
            onError: function(result) {
                alert("Terjadi kesalahan dalam proses pembayaran.");
            },
            onClose: function() {
                alert("Pembayaran belum selesai. Silakan klik 'Bayar Sekarang' untuk melanjutkan.");
                payNow(token);
            }
        });
    }
</script>