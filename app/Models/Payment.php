<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $dates = [
        'paid_at',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->invoice->status) {
            'paid' => 'Lunas',
            'partial' => 'Partial',
            default => $this->invoice->status,
        };
    }

    public function getInvoiceDisplayNumberAttribute(): string
    {
        return $this->invoice->display_number;
    }

    public function getAmountFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 2, ',', '.');
    }
}