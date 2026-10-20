<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestStatusHistory extends Model
{
    use HasFactory;

    #[Fillable([
        'purchase_request_id',
        'user_id',
        'from_status',
        'to_status',
        'comment',
        'reason',
    ])]
    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'from_status',
        'to_status',
        'comment',
        'reason',
    ];

    protected $casts = [
        'from_status' => 'string',
        'to_status' => 'string',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
