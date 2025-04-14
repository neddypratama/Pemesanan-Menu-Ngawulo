<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'resep',
        'created_at',
        'updated_at',
    ];
    
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
