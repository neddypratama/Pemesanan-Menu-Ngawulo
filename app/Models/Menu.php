<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'name',
        'price',
        'deskripsi',
        'stok',
        'kategori_id',
        'created_at',
        'updated_at',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

}
