<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Purchase Order Item Model.
 *
 * Represents an item line on a Purchase Order.
 *
 * @property int $id
 * @property int $purchase_order_id
 * @property string $item_code
 * @property string $description
 * @property integer $quantity
 * @property string $unit
 * @property decimal $unit_price
 * @property decimal $total_price
 * @property string $status
 */
class PurchaseOrderItem extends Model
{
    use HasFactory;

    /** Status item PO */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /** Fillable fields */
    protected $fillable = [
        'purchase_order_id',
        'item_code',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'status',
    ];

    /** Appends */
    protected $appends = [
        'status_label',
    ];

    /** Casts */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the purchase order that this item belongs to.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => $this->status,
        };
    }

    /**
     * Check if item can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if item can be rejected.
     */
    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}