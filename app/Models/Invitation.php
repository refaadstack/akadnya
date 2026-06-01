<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'subdomain',
        'custom_domain',
        'status',
        'published_at',
        'expires_at',
        'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'view_count' => 'integer',
    ];

    // Global scope for data isolation
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->isAdmin()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function content(): HasOne
    {
        return $this->hasOne(InvitationContent::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(InvitationSection::class);
    }

    public function ornaments(): HasMany
    {
        return $this->hasMany(InvitationOrnament::class);
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(InvitationGallery::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    // Scopes
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getPublicUrl(): string
    {
        if ($this->custom_domain) {
            return 'https://'.$this->custom_domain;
        }

        // Use /i/{subdomain} path format (works without wildcard DNS)
        return rtrim(config('app.url'), '/').'/i/'.$this->subdomain;
    }
}
