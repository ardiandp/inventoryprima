<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestItem extends Model
{
    use HasFactory, SoftDeletes;

    // --- Status item RPO (PRD bagian 6) ---
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_REVISION_REQUESTED,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_REVISION_REQUESTED => 'Perlu Revisi',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    
        'purchase_request_id',
        'item_id',
        'unit_id',
        'quantity',
        'unit_price',
        'minimum_term',
        'specification',
        'status',
        'rejection_reason',
        'comment',
    ])]
    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'unit_id',
        'quantity',
        'unit_price',
        'minimum_term',
        'specification',
        'status',
        'rejection_reason',
        'comment',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'minimum_term' => 'decimal:4',
        'status' => 'string',
        'is_active' => 'boolean',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRevision(): bool
    {
        return $this->status === 'revision_requested';
    }
}
