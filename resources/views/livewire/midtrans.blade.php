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
