<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Purchase Order Model.
 *
 * Represents a Purchase Order in the procurement lifecycle:
 * draft → sent → supplier_confirmed → partially_received → fully_received → closed/cancelled
 *
 * @property int $id
 * @property int $rpo_id
 * @property int $supplier_id
 * @property string $po_number
 * @property string $status
 * @property decimal $total_amount
 * @property string $delivery_address
 * @property \DateTime $delivery_date
 * @property string $notes
 * @property int $approved_by
 * @property \DateTime $approved_at
 * @property int $delivered_by
 * @property \DateTime $delivered_at
 * @property \Illuminate\Collection\Collection $items
 * @property \App\Models\PurchaseRequest $rpo
 * @property \App\Models\User $supplier
 * @property \App\Models\User $approver
 * @property \App\Models\Collection|PurchaseOrderHistory $history
 */
class PurchaseOrder extends Model
{
    use HasFactory;

    /** Status PO (PRD bagian 6) */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_SUPPLIER_CONFIRMED = 'supplier_confirmed';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_FULLY_RECEIVED = 'fully_received';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

    /** Fillable fields */
    protected $fillable = [
        'rpo_id',
        'supplier_id',
        'po_number',
        'status',
        'total_amount',
        'delivery_address',
        'delivery_date',
        'notes',
        'approved_by',
        'delivered_by',
    ];

    /** Appends */
    protected $appends = [
        'status_label',
    ];

    /**
     * Get the purchase request that this PO originates from.
     */
    public function rpo(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'rpo_id');
    }

    /**
     * Get the supplier for this PO.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get the user who approved this PO.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who delivered this PO.
     */
    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    /**
     * Get the items for this PO.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the history for this PO.
     */
    public function history(): HasMany
    {
        return $this->hasMany(PurchaseOrderHistory::class);
    }

    /**
     * Get the status label attribute.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_SUPPLIER_CONFIRMED => 'Supplier Konfirmasi',
            self::STATUS_PARTIALLY_RECEIVED => 'Sebagian Diterima',
            self::STATUS_FULLY_RECEIVED => 'Lunas Diterima',
            self::STATUS_CLOSED => 'Tertutup',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Check if PO can be sent.
     */
    public function canBeSent(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if PO can be confirmed by supplier.
     */
    public function canBeConfirmed(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
        ], true);
    }

    /**
     * Check if PO can receive partial delivery.
     */
    public function canReceivePartial(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUPPLIER_CONFIRMED,
            self::STATUS_PARTIALLY_RECEIVED,
        ], true);
    }

    /**
     * Check if PO can be fully received.
     */
    public function canBeFullyReceived(): bool
    {
        return in_array($this->status, [
            self::STATUS_PARTIALLY_RECEIVED,
            self::STATUS_FULLY_RECEIVED,
        ], true);
    }

    /**
     * Check if PO can be closed.
     */
    public function canBeClosed(): bool
    {
        return $this->status === self::STATUS_FULLY_RECEIVED;
    }

    /**
     * Check if PO can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
        ], true);
    }
}