<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    #[Fillable(['code', 'name', 'address', 'contact_person', 'phone', 'is_active'])]
    protected $fillable = ['code', 'name', 'address', 'contact_person', 'phone', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}