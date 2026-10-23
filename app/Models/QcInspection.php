<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'purchase_order_id',
        'display_number',
        'observations',
        'accepted_quantity',
        'rejected_quantity',
        'status',
        'inspected_by',
        'inspected_at',
        'notes',
    ];

    protected $casts = [
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
    ];

    protected $dates = [
        'inspected_at',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'received', 'pending_inspection']);
    }

    public function scopeAccepted($query)
    {
        return $query->whereIn('status', ['partially_accepted', 'accepted']);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draf',
            'received' => 'Diterima',
            'pending_inspection' => 'Menunggu Inspeksi',
            'partially_accepted' => 'Lengkap diterima',
            'accepted' => 'Lolos QC',
            'rejected' => 'Ditolak QC',
            'posted' => 'Sudah Diposting',
            default => $this->status,
        };
    }

    public function getQuantityDiffAttribute(): string
    {
        $diff = $this->accepted_quantity - $this->rejected_quantity;
        return number_format($diff, 2, ',', '.');
    }
}