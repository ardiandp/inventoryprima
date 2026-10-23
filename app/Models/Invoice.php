<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'display_number',
        'amount',
        'tax_amount',
        'total',
        'due_date',
        'status',
        'payment_method',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $dates = [
        'due_date',
        'paid_at',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'partial']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draf',
            'sent' => 'Terkirim',
            'partial' => 'Partial',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            'void' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getDueDateAttribute($value)
    {
        return $value ? jg($value, 'd/m/Y') : '-';
    }
}