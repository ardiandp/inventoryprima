<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_id',
        'production_order_id',
        'display_number',
        'accepted_quantity',
        'returned_quantity',
        'issue_quantity',
        'remaining_quantity',
        'status',
        'notes',
        'performed_by',
        'approved_by',
        'performed_at',
        'approved_at',
    ];

    protected $casts = [
        'accepted_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
        'issue_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    protected $dates = [
        'performed_at',
        'approved_at',
    ];

    public $timestamps = true;

    /**
     * Get the transaction items for this stock transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransactionItem::class);
    }

    /**
     * Get the purchase receipt related to this transaction.
     */
    public function purchaseReceipt(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    /**
     * Get the purchase order related to this transaction.
     */
    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Get the production order related to this transaction.
     */
    public function productionOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by receipt.
     */
    public function scopeByReceipt($query, $receiptId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('purchase_receipt_id', $receiptId);
    }

    /**
     * Get display label for status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'posted' => 'Sudah Diposting',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Check if transaction can be posted.
     */
    public function canBePosted(): bool
    {
        return $this->status === 'draft'
            && $this->issue_quantity > 0
            && $this->remaining_quantity >= 0;
    }

    /**
     * Check if transaction can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'draft' || $this->status === 'posted';
    }
}