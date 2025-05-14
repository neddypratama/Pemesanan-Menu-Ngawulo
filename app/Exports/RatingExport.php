<?php
    namespace App\Exports;
    
    use App\Models\Rating;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\WithHeadings;

    class RatingExport implements FromCollection, WithHeadings
    {
        public function collection()
        {
            return Rating::with('menu')->get()->map(function ($rating) {
                return [
                    $rating->id,
                    $rating->menu->name ?? 'Menu tidak ditemukan',
                    str_repeat('★', $rating->rating) . str_repeat('☆', 5 - $rating->rating), // Menampilkan bintang
                    $rating->review,
                    $rating->created_at->format('Y-m-d H:i:s'),
                ];
            });            
        }

        public function headings(): array
        {
            return ['ID', 'Nama Menu', 'Rating', 'Review', 'Tanggal Dibuat'];
        }
    }