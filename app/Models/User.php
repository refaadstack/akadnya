<?php

namespace App\Models;

use App\Notifications\VerifyEmailViaBrevo;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'active_invitation_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Relationships
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }

    public function activeInvitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class, 'active_invitation_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(UserFeature::class);
    }

    /**
     * @param  array<int, string>  $with
     */
    public function currentInvitation(array $with = []): ?Invitation
    {
        $invitation = null;

        if ($this->active_invitation_id) {
            $invitation = $this->invitations()
                ->with($with)
                ->whereKey($this->active_invitation_id)
                ->first();
        }

        if (! $invitation) {
            $invitation = $this->invitations()
                ->with($with)
                ->latest('id')
                ->first();
        }

        if ($invitation && $this->active_invitation_id !== $invitation->id) {
            $this->forceFill(['active_invitation_id' => $invitation->id])->save();
        }

        return $invitation;
    }

    // Helper methods
    public function hasFeature(string $feature): bool
    {
        return $this->features()
            ->where('feature', $feature)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailViaBrevo);
    }

    // Filament authorization
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
