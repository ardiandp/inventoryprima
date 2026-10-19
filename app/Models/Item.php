<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    #[Fillable([
        'code', 'name', 'category_id', 'default_unit_id', 'specification',
        'min_stock', 'max_stock', 'requires_qc', 'is_active',
    ])]
    protected $fillable = [
        'code', 'name', 'category_id', 'default_unit_id', 'specification',
        'min_stock', 'max_stock', 'requires_qc', 'is_active',
    ];

    protected $casts = [
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
        'requires_qc' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class);
    }
}