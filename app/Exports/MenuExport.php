<?php
namespace App\Exports;

use App\Models\Menu;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class MenuExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return Menu::with('kategori')->get()->map(function ($menu) {
            return [
                $menu->id,
                $menu->kategori->name ?? 'Tidak ada kategori',
                $menu->name,
                $menu->price,
                $menu->stok,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Nama Kategori', 'Nama Menu', 'Harga', 'Stok'];
    }
}
