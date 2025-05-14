<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiExport implements FromCollection, WithHeadings
{
    protected Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return $this->data->flatMap(function ($transaksi) {
            return $transaksi->orders->map(function ($order) use ($transaksi) {
                $harga = $order->menu->price ?? 0;
                $jumlah = $order->qty;
                return [
                    $transaksi->invoice,
                    $order->menu->name ?? '-',
                    $transaksi->tanggal,
                    $jumlah,
                    $harga,
                    $harga * $jumlah,
                    $transaksi->user->name ?? '-', // Nama pembeli
                ];
            });
        });
    }

    public function headings(): array
    {
        return ['Kode Transaksi', 'Nama Menu', 'Tanggal Beli', 'Jumlah', 'Harga Satuan', 'Total Harga', 'Pembeli'];
    }
}
