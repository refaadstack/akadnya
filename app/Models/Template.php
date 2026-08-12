<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'thumbnail_url',
        'version',
        'is_free',
        'price',
        'original_price',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    // Relationships
    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class);
    }

    public function ornaments(): HasMany
    {
        return $this->hasMany(TemplateOrnament::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    // Scopes
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeFree(Builder $query): void
    {
        $query->where('is_free', true);
    }

    // Helper methods
    public function getFolderPath(): string
    {
        return storage_path('app/public/templates/'.$this->slug);
    }

    public function getUsageCount(): int
    {
        return $this->invitations()->count();
    }

    // Helper methods
    public function hasDiscount(): bool
    {
        return ! $this->is_free
            && $this->original_price !== null
            && $this->original_price > 0
            && $this->price < $this->original_price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->hasDiscount()) {
            return 0;
        }

        return (int) round((1 - ($this->price / $this->original_price)) * 100);
    }

    /**
     * Get the full URL for the thumbnail
     */
    public function getThumbnailUrlAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If it starts with http/https, return as is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $url = asset('storage/'.$value);

        // Cache-bust by appending the file mtime so updated thumbnails are never served from browser cache
        $path = storage_path('app/public/'.$value);

        if (is_file($path)) {
            $url .= (str_contains($url, '?') ? '&' : '?').'v='.filemtime($path);
        }

        return $url;
    }

    /**
     * Set the thumbnail URL (for Filament file upload)
     */
    public function setThumbnailUrlAttribute($value): void
    {
        // If it's a full URL, store as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $this->attributes['thumbnail_url'] = $value;
            return;
        }

        // If it starts with http/https, store as is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $this->attributes['thumbnail_url'] = $value;
            return;
        }

        // Otherwise, store the relative path (Filament will handle storage/)
        $this->attributes['thumbnail_url'] = $value;
    }
}
