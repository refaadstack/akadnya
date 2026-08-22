<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'invitation_id',
        'bride_name',
        'bride_nickname',
        'groom_name',
        'groom_nickname',
        'bride_father',
        'bride_mother',
        'bride_photo_url',
        'groom_father',
        'groom_mother',
        'groom_photo_url',
        'cover_name_display',
        'couple_photo_url',
        'akad_datetime',
        'akad_venue',
        'akad_maps_url',
        'reception_datetime',
        'reception_venue',
        'reception_maps_url',
        'show_reception',
        'show_wishes',
        'cover_photo_url',
        'video_url',
        'background_url',
        'music_url',
        'music_title',
        'gallery_photos',
        'love_story',
        'special_message',
        'bank_name',
        'account_number',
        'account_name',
        'qris_image_url',
        'gopay_number',
        'ovo_number',
        'dana_number',
        'gift_address',
    ];

    protected $casts = [
        'akad_datetime' => 'datetime',
        'reception_datetime' => 'datetime',
        'show_reception' => 'boolean',
        'show_wishes' => 'boolean',
        'gallery_photos' => 'array',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
