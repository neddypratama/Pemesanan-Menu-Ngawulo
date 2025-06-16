@php
    \Midtrans\Config::$serverKey = config('midtrans.server_key');
    \Midtrans\Config::$clientKey = config('midtrans.client_key');
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // Pastikan total item = gross_amount
    $item_price = 1000;
    
    $transaction_details = [
        'order_id' => 'ORDER-' . now()->format('YmdHis'),
        'gross_amount' => $item_price,
    ];

    $item_details = [
        [
            'id' => 'a1',
            'price' => $item_price,
            'quantity' => 1,
            'name' => "Apple"
        ],
    ];

    $customer_details = [
        'first_name' => 'Andri',
        'last_name' => 'Litani',
        'email' => 'andri@litani.com',
        'phone' => '081122334455',
        'billing_address' => [
            'first_name' => 'Andri',
            'last_name' => 'Litani',
            'address' => 'Jl. Contoh No. 123',
            'city' => 'Jakarta',
            'postal_code' => '12345',
            'country_code' => 'IDN'
        ]
    ];

    $transaction = [
        'transaction_details' => $transaction_details,
        'item_details' => $item_details,
        'customer_details' => $customer_details,
        'payment_type' => 'qris',
        'qris' => [
            'acquirer' => 'gopay'
        ]
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    } catch (\Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
        logger()->error($errorMessage);
        $snapToken = null;
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <title>Bayar</title>
</head>
<body>
    @if (isset($errorMessage))
        <h3>Error Midtrans: {{ $errorMessage }}</h3>
    @else
        <h2>Total: Rp94.000</h2>
        <button id="pay-button">Bayar Sekarang</button>

        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function() {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result){
                        alert('Pembayaran berhasil!');
                        console.log(result);
                    },
                    onPending: function(result){
                        alert('Menunggu pembayaran...');
                        console.log(result);
                    },
                    onError: function(result){
                        alert('Pembayaran gagal!');
                        console.log(result);
                    },
                    onClose: function(){
                        alert('Kamu belum menyelesaikan pembayaran.');
                    }
                });
            };
        </script>
    @endif
</body>
</html>
