<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\SiteSetting;
use App\Models\Template;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
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
    public function build(Invitation $invitation, ?string $guestName = null, ?string $guestCode = null): array
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
            ->where('is_hidden', false)
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

        $groomInitial = $this->initialOf($content?->groom_nickname) ?? $this->initialOf($content?->groom_name);
        $brideInitial = $this->initialOf($content?->bride_nickname) ?? $this->initialOf($content?->bride_name);

        // Base contract with all keys guaranteed to exist
        $contract = [
            // Bride
            'bride_name' => $content?->bride_name ?? null,
            'bride_nickname' => $content?->bride_nickname ?? null,
            'bride_father' => $content?->bride_father ?? null,
            'bride_mother' => $content?->bride_mother ?? null,
            'bride_photo_url' => $content?->bride_photo_url ?? null,
            'bride_initial' => $brideInitial,

            // Groom
            'groom_name' => $content?->groom_name ?? null,
            'groom_nickname' => $content?->groom_nickname ?? null,
            'groom_father' => $content?->groom_father ?? null,
            'groom_mother' => $content?->groom_mother ?? null,
            'groom_photo_url' => $content?->groom_photo_url ?? null,
            'groom_initial' => $groomInitial,
            'cover_name_display' => $content?->cover_name_display ?? 'full',
            'show_wishes' => $content?->show_wishes ?? true,
            'cover_names' => $this->coverNames(
                $content?->cover_name_display ?? 'full',
                $content?->bride_name,
                $content?->groom_name,
                $content?->bride_nickname,
                $content?->groom_nickname,
                $brideInitial,
                $groomInitial
            ),
            'couple_photo_url' => $content?->couple_photo_url ?? null,
            'couple_initials' => $groomInitial && $brideInitial ? "{$groomInitial} & {$brideInitial}" : null,

            // Akad venue
            'akad_venue' => $content?->akad_venue ?? null,
            'akad_maps_url' => $content?->akad_maps_url ?? null,

            // Reception venue
            'reception_venue' => $content?->reception_venue ?? null,
            'reception_maps_url' => $content?->reception_maps_url ?? null,
            'show_reception' => $content?->show_reception ?? true,

            // Media
            'cover_photo_url' => $content?->cover_photo_url ?? null,
            'video_url' => $content?->video_url ?? null,
            'video_youtube_id' => $this->extractYoutubeId($content?->video_url),
            'background_url' => $content?->background_url ?? null,
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
            'guest' => null,
            'guest_book_enabled' => false,
            'guest_qr_svg' => null,
            'guest_qr_demo' => null,
            'guest_book_url' => route('products.index'),

            // Sponsorship
            'sponsored_by' => $invitation->user?->hasTemplateAccess($invitation->template) ?? false,

            // Event dates (ISO 8601 format for JavaScript countdown)
            'akad_datetime' => $content?->akad_datetime?->toIso8601String() ?? null,
            'event_date' => ($content?->show_reception ?? true) && $content?->reception_datetime
                ? $content->reception_datetime->toIso8601String()
                : ($content?->akad_datetime?->toIso8601String() ?? null),
        ];

        // Guest book (venue) data
        $guestBookEnabled = $invitation->user?->hasFeature('guest_book') ?? false;
        if ($guestBookEnabled) {
            $contract['guest_book_enabled'] = true;

            $guest = $guestCode
                ? $invitation->guests()->where('unique_code', $guestCode)->first()
                : null;

            if ($guest) {
                $contract['guest'] = [
                    'id' => $guest->id,
                    'name' => $guest->name,
                    'unique_code' => $guest->unique_code,
                    'category' => $guest->category,
                    'max_pax' => $guest->max_pax,
                ];
                $contract['guest_qr_svg'] = $this->buildGuestQrSvg($guest->unique_code);
            }
        }

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

        $contract = $this->hydrateDatetimeVariables($contract);
        $contract['video_youtube_id'] = $this->extractYoutubeId($contract['video_url'] ?? null);
        $contract['guest_qr_demo'] = $this->buildDemoGuestQrSvg();

        return $this->hydrateCoverNames($this->hydrateInitials($contract));
    }

    /**
     * Build a deterministic demo QR code shown in template previews
     * and the landing page guest book promo.
     */
    public function buildDemoGuestQrSvg(): ?string
    {
        return $this->buildGuestQrSvg('MyAkad-DEMO-0001');
    }

    /**
     * Compute the cover names from the configured display mode.
     *
     * @param  array<string, mixed>  $contract
     * @return array<string, mixed>
     */
    protected function hydrateCoverNames(array $contract): array
    {
        $contract['cover_names'] = $this->coverNames(
            $contract['cover_name_display'] ?? 'full',
            $contract['bride_name'] ?? null,
            $contract['groom_name'] ?? null,
            $contract['bride_nickname'] ?? null,
            $contract['groom_nickname'] ?? null,
            $contract['bride_initial'] ?? null,
            $contract['groom_initial'] ?? null
        );

        return $contract;
    }

    /**
     * Build the cover name string based on the display mode.
     */
    protected function coverNames(
        string $mode,
        ?string $brideName,
        ?string $groomName,
        ?string $brideNickname,
        ?string $groomNickname,
        ?string $brideInitial,
        ?string $groomInitial
    ): ?string {
        $bride = match ($mode) {
            'initials' => $brideInitial,
            'nickname' => $brideNickname ?: $brideName,
            default => $brideName,
        };

        $groom = match ($mode) {
            'initials' => $groomInitial,
            'nickname' => $groomNickname ?: $groomName,
            default => $groomName,
        };

        if (! $bride || ! $groom) {
            return null;
        }

        return trim($bride.' & '.$groom);
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
            'bride_nickname' => null,
            'bride_father' => null,
            'bride_mother' => null,
            'bride_photo_url' => null,
            'bride_initial' => null,
            'groom_name' => null,
            'groom_nickname' => null,
            'groom_father' => null,
            'groom_mother' => null,
            'groom_photo_url' => null,
            'groom_initial' => null,
            'cover_name_display' => 'full',
            'show_wishes' => true,
            'cover_names' => null,
            'couple_photo_url' => null,
            'couple_initials' => null,
            'akad_datetime' => null,
            'akad_venue' => null,
            'akad_maps_url' => null,
            'reception_datetime' => null,
            'reception_venue' => null,
            'reception_maps_url' => null,
            'cover_photo_url' => null,
            'video_url' => null,
            'video_youtube_id' => null,
            'background_url' => null,
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
            'guest' => null,
            'guest_book_enabled' => false,
            'guest_qr_svg' => null,
            'guest_qr_demo' => null,
            'guest_book_url' => route('products.index'),
            'sponsored_by' => false,
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

    /**
     * @param  array<string, mixed>  $contract
     * @return array<string, mixed>
     */
    protected function hydrateInitials(array $contract): array
    {
        $groom = $this->initialOf($contract['groom_nickname'] ?? null) ?? $this->initialOf($contract['groom_name'] ?? null);
        $bride = $this->initialOf($contract['bride_nickname'] ?? null) ?? $this->initialOf($contract['bride_name'] ?? null);

        $contract['groom_initial'] = $groom;
        $contract['bride_initial'] = $bride;
        $contract['couple_initials'] = $groom && $bride ? "{$groom} & {$bride}" : null;

        return $contract;
    }

    protected function initialOf(mixed $name): ?string
    {
        $name = trim((string) ($name ?? ''));

        if ($name === '') {
            return null;
        }

        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    /**
     * Generate an SVG QR code for the given payload.
     */
    public function buildGuestQrSvg(string $payload): ?string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(300, 1),
                new SvgImageBackEnd
            );

            $svg = (new Writer($renderer))->writeString(
                $payload,
                Encoder::DEFAULT_BYTE_MODE_ENCODING,
                ErrorCorrectionLevel::Q()
            );

            // Embed the brand logo in the center of the QR code.
            // The logo is inlined as a data URI when it resolves to a local file so the
            // QR stays fully self-contained (mobile browsers/proxies may block or skip
            // external sub-resources inside inline SVG).
            $logoHref = $this->resolveGuestQrLogoDataUri() ?? SiteSetting::get('qr_logo_url') ?? url('/favicon.svg');
            $logoBlock = '<g>'
                .'<rect x="117" y="117" width="66" height="66" rx="12" fill="#ffffff"/>'
                .sprintf('<image x="122" y="122" width="56" height="56" href="%s" xlink:href="%s" preserveAspectRatio="xMidYMid meet"/>', e($logoHref), e($logoHref))
                .'</g>';

            $svg = str_replace('</svg>', $logoBlock.'</svg>', $svg);

            return str_replace(
                '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="300" height="300"',
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="180" height="180"',
                $svg
            );
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Inline the guest QR brand logo as a base64 data URI when it points to a local
     * public file. Returns null for remote/missing files so callers fall back to URLs.
     */
    protected function resolveGuestQrLogoDataUri(): ?string
    {
        $logoUrl = SiteSetting::get('qr_logo_url');

        if (! is_string($logoUrl) || $logoUrl === '') {
            $logoUrl = '/favicon.svg';
        }

        $path = (string) (parse_url($logoUrl, PHP_URL_PATH) ?: '');

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        $file = public_path(ltrim($path, '/'));

        if (! is_file($file) || filesize($file) > 512 * 1024) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false || $contents === '') {
            return null;
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';

        if (! str_starts_with((string) $mime, 'image/')) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mime, base64_encode($contents));
    }

    /**
     * Extract the 11-character YouTube video ID from common URL formats.
     */
    public function extractYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        preg_match(
            '/(?:youtube(?:-nocookie)?\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/',
            $url,
            $matches
        );

        return $matches[1] ?? null;
    }
}
