<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationOrnament extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'template_ornament_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function templateOrnament(): BelongsTo
    {
        return $this->belongsTo(TemplateOrnament::class);
    }
}
