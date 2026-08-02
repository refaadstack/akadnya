<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Template;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

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
        $gallery = $invitation->gallery()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($item) => [
                'url' => $item->image_url,
                'caption' => $item->caption,
            ]);

        $contentGallery = collect($content?->gallery_photos ?? [])
            ->filter(fn ($item) => is_array($item) && ! empty($item['url']))
            ->map(fn ($item) => [
                'url' => $item['url'],
                'caption' => $item['caption'] ?? null,
            ]);

        $loveStories = $invitation->loveStories
            ->map(fn ($story) => [
                'date' => $story->date_label,
                'title' => $story->title,
                'description' => $story->description,
            ])
            ->values();

        $wishes = $invitation->rsvps()
            ->whereNotNull('message')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($rsvp) => [
                'id' => $rsvp->id,
                'name' => $rsvp->name ?? $rsvp->guest?->name ?? 'Tamu',
                'message' => $rsvp->message,
                'attendance' => $rsvp->attendance,
                'created_at' => $rsvp->created_at?->toISOString(),
            ])
            ->values();

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
            'couple_photo_url' => $content?->couple_photo_url ?? null,

            // Akad venue
            'akad_venue' => $content?->akad_venue ?? null,
            'akad_maps_url' => $content?->akad_maps_url ?? null,

            // Reception venue
            'reception_venue' => $content?->reception_venue ?? null,
            'reception_maps_url' => $content?->reception_maps_url ?? null,

            // Media
            'cover_photo_url' => $content?->cover_photo_url ?? null,
            'music_url' => $content?->music_url ?? null,
            'music_title' => $content?->music_title ?? null,

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
            'gift_address' => $content?->gift_address ?? null,

            // Gallery
            'gallery' => $gallery->concat($contentGallery)->values()->toArray(),

            // Love stories timeline
            'love_stories' => $loveStories->toArray(),

            // Wishes / doa & ucapan
            'wishes' => $wishes->toArray(),

            // RSVP
            'rsvp_action' => route('invitation.rsvp', ['subdomain' => $invitation->subdomain]),
            'csrf_token' => csrf_token() ?? '',

            // Guest
            'guest_name' => $guestName ?? null,

            // Event dates (ISO 8601 format for JavaScript countdown)
            'akad_datetime' => $content?->akad_datetime?->toIso8601String() ?? null,
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
     * Build preview Data Contract from template-owned defaults.
     *
     * @return array<string, mixed>
     */
    public function buildTemplateDefaults(Template $template): array
    {
        $contract = array_replace($this->buildEmptyPreviewContract(), $this->readTemplateDefaults($template));

        return $this->hydrateDatetimeVariables($contract);
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

    /**
     * @return array<string, mixed>
     */
    protected function buildEmptyPreviewContract(): array
    {
        return [
            'bride_name' => null,
            'bride_father' => null,
            'bride_mother' => null,
            'bride_photo_url' => null,
            'groom_name' => null,
            'groom_father' => null,
            'groom_mother' => null,
            'groom_photo_url' => null,
            'couple_photo_url' => null,
            'akad_datetime' => null,
            'akad_venue' => null,
            'akad_maps_url' => null,
            'reception_datetime' => null,
            'reception_venue' => null,
            'reception_maps_url' => null,
            'cover_photo_url' => null,
            'music_url' => null,
            'music_title' => null,
            'love_story' => null,
            'special_message' => null,
            'bank_name' => null,
            'account_number' => null,
            'account_name' => null,
            'qris_image_url' => null,
            'gopay_number' => null,
            'ovo_number' => null,
            'dana_number' => null,
            'gift_address' => null,
            'gallery' => [],
            'love_stories' => [],
            'wishes' => [],
            'rsvp_action' => '#',
            'csrf_token' => csrf_token() ?? '',
            'guest_name' => null,
            'akad_datetime' => null,
            'event_date' => null,
            ...$this->buildDatetimeVariables('akad', null),
            ...$this->buildDatetimeVariables('reception', null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function readTemplateDefaults(Template $template): array
    {
        $jsonPath = $template->getFolderPath().'/template.json';

        if (! File::exists($jsonPath)) {
            return [];
        }

        try {
            $config = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($config['defaults'] ?? null) ? $config['defaults'] : [];
    }

    /**
     * @param  array<string, mixed>  $contract
     * @return array<string, mixed>
     */
    protected function hydrateDatetimeVariables(array $contract): array
    {
        $akadDate = $this->parsePreviewDatetime($contract['akad_datetime'] ?? null);
        if ($akadDate) {
            $contract = array_replace($contract, $this->buildDatetimeVariables('akad', $akadDate));
            $contract['akad_datetime'] = $akadDate->toIso8601String();
        }

        $receptionDate = $this->parsePreviewDatetime($contract['reception_datetime'] ?? null);
        if ($receptionDate) {
            $contract = array_replace($contract, $this->buildDatetimeVariables('reception', $receptionDate));
            $contract['event_date'] = $contract['event_date'] ?: $receptionDate->toIso8601String();
        }

        return $contract;
    }

    protected function parsePreviewDatetime(mixed $value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return $value instanceof Carbon ? $value : Carbon::instance($value);
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
