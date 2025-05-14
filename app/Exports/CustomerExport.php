<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class CustomerExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return User::where('role_id', 4)->get()->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Nama', 'Email', 'Tanggal Daftar'];
    }
}
