<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    #[Fillable([
        'file_name', 'file_path', 'disk', 'mime_type', 'file_size',
        'reference_type', 'reference_id', 'uploaded_by',
    ])]
    protected $fillable = [
        'file_name', 'file_path', 'disk', 'mime_type', 'file_size',
        'reference_type', 'reference_id', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}