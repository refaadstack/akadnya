<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'name',
        'phone',
        'category',
        'unique_code',
        'max_pax',
        'notes',
    ];

    protected $casts = [
        'max_pax' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($guest) {
            if (empty($guest->unique_code)) {
                $guest->unique_code = Str::random(20);
            }
        });
    }

    // Relationships
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }

    // Helper methods
    public function getPersonalLink(): string
    {
        $invitation = $this->invitation;

        // Encode guest name for URL
        $guestName = urlencode($this->name);

        // Use custom domain if available, otherwise use subdomain
        if ($invitation->custom_domain) {
            return 'https://'.$invitation->custom_domain.'?name='.$guestName;
        }

        // Use /i/{subdomain} route for public invitations
        return url('/i/'.$invitation->subdomain.'?name='.$guestName);
    }

    public function hasRsvp(): bool
    {
        return $this->rsvp()->exists();
    }
}
