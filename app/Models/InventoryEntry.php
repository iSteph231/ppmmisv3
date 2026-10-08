<?php

namespace App\Models;

use Database\Factories\InventoryEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryEntry extends Model
{
    /** @use HasFactory<InventoryEntryFactory> */
    use HasFactory;

    protected $fillable = ['form', 'user_id', 'data'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }
}
