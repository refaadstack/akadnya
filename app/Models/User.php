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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role'])]
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(UserFeature::class);
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
