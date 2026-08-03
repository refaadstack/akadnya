<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "item_type",
        "item_id",
        "quantity",
        "preview_data",
    ];

    protected $casts = [
        "quantity" => "integer",
        "preview_data" => "array",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve the underlying template or product model.
     */
    public function model(): Template|Product|null
    {
        return match ($this->item_type) {
            "template" => Template::find($this->item_id),
            "product" => Product::find($this->item_id),
            default => null,
        };
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where("user_id", $user->id);
    }
}
