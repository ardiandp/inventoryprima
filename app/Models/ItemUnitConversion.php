<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemUnitConversion extends Model
{
    use HasFactory;

    #[Fillable(['item_id', 'from_unit_id', 'to_unit_id', 'conversion_factor'])]
    protected $fillable = ['item_id', 'from_unit_id', 'to_unit_id', 'conversion_factor'];

    protected $casts = [
        'conversion_factor' => 'decimal:6',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}