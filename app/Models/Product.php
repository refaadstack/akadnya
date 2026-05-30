<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'slug',
        'name',
        'description',
        'price',
        'is_active',
        'is_recurring',
        'recurring_interval',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeBasePackage(Builder $query): void
    {
        $query->where('type', 'base_package');
    }

    public function scopeAddons(Builder $query): void
    {
        $query->where('type', 'addon');
    }
}
