<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transaction_id',
        'material_id',
        'material_name',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the stock transaction that owns this item.
     */
    public function stockTransaction(): BelongsTo
    {
        return $this->belongsTo(StockTransaction::class, 'stock_transaction_id');
    }

    /**
     * Get display label for unit.
     */
    public function getUnitLabelAttribute(): string
    {
        return $this->unit ?? 'pcs';
    }

    /**
     * Calculate total price.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
}