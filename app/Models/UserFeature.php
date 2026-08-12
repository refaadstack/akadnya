<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeature extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'feature',
        'order_item_id',
        'metadata',
        'setup_status',
        'setup_notes',
        'setup_updated_at',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'setup_updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (UserFeature $feature) {
            if ($feature->feature === 'managed_setup' && $feature->setup_status === null) {
                $feature->setup_status = 'pending';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function isActive(): bool
    {
        return is_null($this->expires_at) || $this->expires_at->isFuture();
    }
}
