<?php

namespace App\Services;

use App\Models\Invitation;
use Carbon\Carbon;

/**
 * Builds the Data Contract array for template rendering.
 *
 * Ensures all variables are always present (never undefined),
 * even if their values are null or empty strings.
 */
class DataContractBuilder
{
    /**
     * Build Data Contract from a real Invitation.
     *
     * @return array<string, mixed>
     */
    public function build(Invitation $invitation, ?string $guestName = null): array
    {
        $content = $invitation->content;
        $gallery = $invitation->gallery()->orderBy('sort_order')->get();

        // Base contract with all keys guaranteed to exist
        $contract = [
            // Bride
            'bride_name' => $content?->bride_name ?? null,
            'bride_father' => $content?->bride_father ?? null,
            'bride_mother' => $content?->bride_mother ?? null,
            'bride_photo_url' => $content?->bride_photo_url ?? null,

            // Groom
            'groom_name' => $content?->groom_name ?? null,
            'groom_father' => $content?->groom_father ?? null,
            'groom_mother' => $content?->groom_mother ?? null,
            'groom_photo_url' => $content?->groom_photo_url ?? null,

            // Akad venue
            'akad_venue' => $content?->akad_venue ?? null,
            'akad_maps_url' => $content?->akad_maps_url ?? null,

            // Reception venue
            'reception_venue' => $content?->reception_venue ?? null,
            'reception_maps_url' => $content?->reception_maps_url ?? null,

            // Media
            'cover_photo_url' => $content?->cover_photo_url ?? null,
            'music_url' => $content?->music_url ?? null,

            // Content
            'love_story' => $content?->love_story ?? null,
            'special_message' => $content?->special_message ?? null,

            // Payment info
            'bank_name' => $content?->bank_name ?? null,
            'account_number' => $content?->account_number ?? null,
            'account_name' => $content?->account_name ?? null,
            'qris_image_url' => $content?->qris_image_url ?? null,
            'gopay_number' => $content?->gopay_number ?? null,
            'ovo_number' => $content?->ovo_number ?? null,
            'dana_number' => $content?->dana_number ?? null,

            // Gallery
            'gallery' => $gallery->map(fn ($item) => [
                'url' => $item->image_url,
                'caption' => $item->caption,
            ])->toArray(),

            // RSVP
            'rsvp_action' => route('invitation.rsvp', ['subdomain' => $invitation->subdomain]),
            'csrf_token' => csrf_token() ?? '',

            // Guest
            'guest_name' => $guestName ?? null,

            // Event date for countdown (ISO 8601 format for JavaScript)
            'event_date' => $content?->reception_datetime?->toIso8601String() ?? null,
        ];

        // Merge datetime variables for akad
        $contract = array_merge(
            $contract,
            $this->buildDatetimeVariables('akad', $content?->akad_datetime)
        );

        // Merge datetime variables for reception
        $contract = array_merge(
            $contract,
            $this->buildDatetimeVariables('reception', $content?->reception_datetime)
        );

        return $contract;
    }

    /**
     * Build Data Contract with dummy data for preview.
     *
     * @return array<string, mixed>
     */
    public function buildDummy(): array
    {
        $dummyAkadDate = Carbon::parse('2025-06-14 09:00:00');
        $dummyReceptionDate = Carbon::parse('2025-06-14 13:00:00');

        $contract = [
            // Bride
            'bride_name' => 'Siti Nurhaliza',
            'bride_father' => 'Bapak Ahmad Nurdin',
            'bride_mother' => 'Ibu Siti Aminah',
            'bride_photo_url' => 'https://via.placeholder.com/400x400/FFD700/8B4513?text=Bride',

            // Groom
            'groom_name' => 'Budi Santoso',
            'groom_father' => 'Bapak Santoso Wijaya',
            'groom_mother' => 'Ibu Dewi Lestari',
            'groom_photo_url' => 'https://via.placeholder.com/400x400/DC143C/FFFAF0?text=Groom',

            // Akad venue
            'akad_venue' => 'Masjid Al-Ikhlas, Jl. Merdeka No. 123, Jakarta Selatan',
            'akad_maps_url' => 'https://maps.google.com/?q=-6.2088,106.8456',

            // Reception venue
            'reception_venue' => 'Gedung Serbaguna Melati, Jl. Sudirman No. 456, Jakarta Pusat',
            'reception_maps_url' => 'https://maps.google.com/?q=-6.2088,106.8456',

            // Media
            'cover_photo_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=1200&h=800&fit=crop',
            'music_url' => asset('audio/dummy/wedding-song.mp3'),

            // Content
            'love_story' => 'Kami bertemu pertama kali di kampus pada tahun 2020. Dari pertemanan biasa, kami mulai saling mengenal lebih dalam dan akhirnya memutuskan untuk melanjutkan ke jenjang yang lebih serius.',
            'special_message' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu kepada kami.',

            // Payment info
            'bank_name' => 'Bank Mandiri',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
            'qris_image_url' => 'https://via.placeholder.com/300x300/FFFFFF/000000?text=QRIS',
            'gopay_number' => '081234567890',
            'ovo_number' => '081234567890',
            'dana_number' => '081234567890',

            // Gallery
            'gallery' => [
                ['url' => 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Photo+1', 'caption' => 'Foto Prewedding 1'],
                ['url' => 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Photo+2', 'caption' => 'Foto Prewedding 2'],
                ['url' => 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Photo+3', 'caption' => 'Foto Prewedding 3'],
                ['url' => 'https://via.placeholder.com/400x300/FFB6C1/FFFFFF?text=Photo+4', 'caption' => 'Foto Prewedding 4'],
            ],

            // RSVP
            'rsvp_action' => '#',
            'csrf_token' => csrf_token() ?? '',

            // Guest
            'guest_name' => 'Tamu Undangan',

            // Event date for countdown (ISO 8601 format for JavaScript)
            'event_date' => $dummyReceptionDate->toIso8601String(),
        ];

        // Merge datetime variables
        $contract = array_merge(
            $contract,
            $this->buildDatetimeVariables('akad', $dummyAkadDate)
        );

        $contract = array_merge(
            $contract,
            $this->buildDatetimeVariables('reception', $dummyReceptionDate)
        );

        return $contract;
    }

    /**
     * Build datetime-derived variables for a given prefix.
     *
     * @return array<string, string|null>
     */
    public function buildDatetimeVariables(string $prefix, ?\DateTimeInterface $datetime): array
    {
        if (! $datetime) {
            return [
                "{$prefix}_datetime_formatted" => null,
                "{$prefix}_time" => null,
                "{$prefix}_date" => null,
                "{$prefix}_month" => null,
                "{$prefix}_year" => null,
                "{$prefix}_day" => null,
            ];
        }

        // Convert to Carbon if needed
        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::instance($datetime);

        // Set locale to Indonesian
        $carbon->locale('id');

        return [
            "{$prefix}_datetime_formatted" => $carbon->isoFormat('dddd, D MMMM YYYY'),
            "{$prefix}_time" => $carbon->format('H:i').' WIB',
            "{$prefix}_date" => $carbon->format('d'),
            "{$prefix}_month" => $carbon->isoFormat('MMMM'),
            "{$prefix}_year" => $carbon->format('Y'),
            "{$prefix}_day" => $carbon->isoFormat('dddd'),
        ];
    }
}
