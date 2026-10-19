<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    #[Fillable(['code', 'name', 'contact_person', 'email', 'phone', 'address', 'tax_id', 'is_active'])]
    protected $fillable = ['code', 'name', 'contact_person', 'email', 'phone', 'address', 'tax_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}