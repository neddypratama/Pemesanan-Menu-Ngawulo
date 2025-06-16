<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Midtrans\Config;
use Midtrans\Snap;

new #[Layout('components.layouts.empty')] class extends Component
{
    public ?string $snapToken = null;

    public function bayarSekarang()
    {
        // Setup Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => uniqid('ORDER-'),
                'gross_amount' => 10000,
            ],
            'customer_details' => [
                'first_name' => 'Neddy',
                'email' => 'neddy@example.com',
            ],
            'item_details' => [
                [
                    'id' => 'item-1',
                    'price' => 10000,
                    'quantity' => 1,
                    'name' => 'Tes Menu',
                ]
            ]
        ];

        try {
            $this->snapToken = Snap::getSnapToken($params);

            $this->dispatch('snap-token-generated', snapToken: $this->snapToken);
        } catch (\Exception $e) {
            $this->addError('snapToken', 'Gagal membuat token Midtrans: ' . $e->getMessage());
        }
    }

    public function paymentSuccess($result)
    {
        session()->flash('success', 'Pembayaran berhasil! Order ID: ' . ($result['order_id'] ?? '-'));
    }

    public function paymentPending($result)
    {
        session()->flash('info', 'Pembayaran masih pending. Order ID: ' . ($result['order_id'] ?? '-'));
    }

    public function paymentError($result)
    {
        session()->flash('error', 'Terjadi kesalahan saat pembayaran.');
    }
};
?>
<div class="p-10">
    @if (session('success'))
        <div class="p-4 mb-4 rounded bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @elseif (session('info'))
        <div class="p-4 mb-4 rounded bg-yellow-100 text-yellow-700">
            {{ session('info') }}
        </div>
    @elseif (session('error'))
        <div class="p-4 mb-4 rounded bg-red-100 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <x-button label="Bayar Sekarang"
              wire:click="bayarSekarang"
              spinner="bayarSekarang"
              class="btn btn-primary" />

    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        document.addEventListener('snap-token-generated', event => {
            const token = event.detail.snapToken;
            if (!token) return alert("Snap token tidak tersedia!");

            snap.pay(token, {
                onSuccess: result => {
                    Livewire.dispatch('paymentSuccess', result);
                },
                onPending: result => {
                    Livewire.dispatch('paymentPending', result);
                },
                onError: result => {
                    Livewire.dispatch('paymentError', result);
                },
                onClose: () => {
                    alert("Kamu menutup popup tanpa menyelesaikan pembayaran.");
                }
            });
        });
    </script>
</div>


<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Transaksi;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Str;

#[Layout('layouts.app')]  // Contoh layout Mary UI
class Checkout extends Component
{
    public Transaksi $transaksi;
    public array $orders = [];
    public string $snapToken = '';
    public bool $showCatatanModal = false;
    public string $catatan = '';
    public ?int $selectedCartItemId = null;

    private int $retryCount = 0;
    private int $maxRetry = 3;

    public function mount(string $invoice)
    {
        $this->transaksi = Transaksi::with(['orders.menu', 'user'])->where('invoice', $invoice)->firstOrFail();

        if ($this->transaksi->status === 'success') {
            redirect()->route('orders.show', $this->transaksi->id)->with('status', 'Pembayaran telah dilakukan.')->send();
            return;
        }

        $this->orders = $this->transaksi->orders->toArray();

        $this->configureMidtrans();

        try {
            if ($this->transaksi->token && $this->transaksi->midtrans_id) {
                $status = Transaction::status($this->transaksi->midtrans_id);

                switch ($status->transaction_status) {
                    case 'pending':
                    case 'capture':
                    case 'authorize':
                        $this->snapToken = $this->transaksi->token;
                        break;
                    case 'settlement':
                        $this->transaksi->update(['status' => 'success']);
                        redirect()->route('orders.show', $this->transaksi->id)->with('status', 'Pembayaran telah dilakukan.')->send();
                        break;
                    case 'cancel':
                        $this->transaksi->update(['status' => 'cancel']);
                        break;
                    case 'expire':
                    case 'deny':
                        $this->transaksi->update(['status' => 'error']);
                        break;
                    default:
                        $this->generateSnapToken(true);
                        break;
                }
            } else {
                $this->generateSnapToken();
            }
        } catch (\Exception $e) {
            logActivity('error', 'Gagal cek status Midtrans: ' . $e->getMessage());
            $this->generateSnapToken(true);
        }
    }

    public function configureMidtrans(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function generateSnapToken(bool $forceNewOrderId = false): void
    {
        $orderId = $forceNewOrderId || !$this->transaksi->midtrans_id
            ? 'ORDER-' . now()->format('YmdHis') . '-' . Str::random(4)
            : $this->transaksi->midtrans_id;

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
            $token = Snap::getSnapToken($params);
            $this->transaksi->update([
                'token' => $token,
                'midtrans_id' => $orderId,
                'status' => 'pending',
            ]);
            logActivity('pending', 'Membuat Snap Token transaksi ' . $this->transaksi->invoice);
            $this->snapToken = $token;
            $this->retryCount = 0;
        } catch (\Exception $e) {
            $this->retryCount++;
            logActivity('error', 'Gagal membuat Snap Token (Attempt ' . $this->retryCount . '): ' . $e->getMessage());

            if ($this->retryCount <= $this->maxRetry) {
                $this->generateSnapToken(true);
            } else {
                $this->snapToken = '';
                session()->flash('error', 'Gagal membuat token pembayaran. Silakan coba lagi nanti.');
            }
        }
    }

    public function openCatatanModal(int $orderId)
    {
        $this->selectedCartItemId = $orderId;
        $order = Order::find($orderId);
        $this->catatan = $order ? $order->keterangan : '';
        $this->showCatatanModal = true;
    }

    public function updatePaymentStatus(array $result)
    {
        $transaksi = Transaksi::where('midtrans_id', $result['order_id'] ?? '')->first();
        if (!$transaksi) return;

        switch ($result['transaction_status'] ?? '') {
            case 'settlement':
                $transaksi->update(['status' => 'success']);
                break;
            case 'deny':
            case 'expire':
                $transaksi->update(['status' => 'error']);
                break;
            case 'cancel':
                $transaksi->update(['status' => 'cancel']);
                break;
        }

        logActivity($result['transaction_status'] ?? 'unknown', 'Update status transaksi: ' . $transaksi->invoice);
        redirect()->route('orders.show', $transaksi->id)->send();
    }

    public function payNow()
    {
        if (!$this->snapToken) {
            session()->flash('error', 'Token pembayaran belum tersedia, silakan coba lagi nanti.');
            return;
        }

        // Memanggil Snap.js di client side, ini hanya contoh trigger JS, sebenarnya pakai Livewire Emit
        $this->dispatchBrowserEvent('midtrans-pay', ['token' => $this->snapToken]);
    }

    public function template(): string
    {
        // Gunakan PHP native untuk markup dan control flow
        ob_start(); ?>
        <div class="mx-auto">
            <?php if (session('status')): ?>
                <div class="alert alert-success"><?= session('status') ?></div>
            <?php elseif (session('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-3 flex items-center justify-center">
                    <img src="https://orange.mary-ui.com/images/checkout.png" class="w-80" />
                </div>

                <div class="lg:col-span-5">
                    <div class="card p-6 rounded-2xl shadow">
                        <h2 class="text-lg font-bold mb-4">Checkout</h2>
                        <hr class="mb-5">

                        <?php foreach ($this->orders as $order): ?>
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center">
                                    <img src="<?= htmlspecialchars($order['menu']['photo']) ?>" class="w-12 h-12 rounded" />
                                    <div>
                                        <p class="font-semibold ml-5"><?= htmlspecialchars($order['menu']['name']) ?></p>
                                        <p class="text-sm ml-5 font-thin">Rp. <?= number_format($order['menu']['price'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <button class="btn btn-sm btn-outline" wire:click="openCatatanModal(<?= $order['id'] ?>)">
                                        Catatan
                                    </button>
                                    <span class="ml-5 font-bold"><?= $order['qty'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr class="my-5">
                        <div class="flex justify-between">
                            <div>Total</div>
                            <div class="font-bold text-lg">Rp. <?= number_format($this->transaksi->total, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="card p-6 rounded-2xl shadow flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-bold mb-4">Pembayaran</h2>
                            <hr class="my-5">
                            <p class="text-sm">Klik tombol "Bayar Sekarang" untuk melanjutkan pembayaran.</p>
                        </div>
                        <button class="btn btn-primary w-full mt-6 py-2 justify-center"
                                <?= $this->snapToken ? 'onclick="payNow()"' : 'disabled' ?>>
                            Bayar Sekarang
                        </button>
                    </div>
                </div>

                <?php if ($this->showCatatanModal): ?>
                    <div class="modal" style="display: block;">
                        <div class="modal-content">
                            <h3>Catatan Pesanan</h3>
                            <textarea readonly><?= htmlspecialchars($this->catatan) ?></textarea>
                            <button wire:click="$set('showCatatanModal', false)">Tutup</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= config('midtrans.client_key') ?>"></script>
        <script>
            function payNow() {
                window.snap.pay('<?= $this->snapToken ?>', {
                    onSuccess: function(result) {
                        Livewire.emit('updatePaymentStatus', result);
                    },
                    onPending: function() {
                        alert('Pembayaran masih diproses. Silakan cek halaman My Order.');
                    },
                    onError: function() {
                        alert('Terjadi kesalahan saat memproses pembayaran.');
                    },
                    onClose: function() {
                        alert('Pembayaran dibatalkan. Klik "Bayar Sekarang" untuk melanjutkan.');
                    }
                });
            }

            window.addEventListener('midtrans-pay', event => {
                payNow();
            });
        </script>
        <?php return ob_get_clean();
    }
}
