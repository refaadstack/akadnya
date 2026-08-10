<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\SiteSetting;

/**
 * Builds and injects SEO meta tags into public invitation HTML.
 */
class SeoMetaService
{
    /**
     * Build SEO data for a public invitation page.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function forInvitation(Invitation $invitation, array $data): array
    {
        $bride = trim((string) ($data['bride_name'] ?? ''));
        $groom = trim((string) ($data['groom_name'] ?? ''));
        $couple = $bride !== '' && $groom !== '' ? "{$bride} & {$groom}" : ($bride ?: $groom);

        $siteName = config('app.name', 'MyAkad');
        $title = $couple !== ''
            ? "Undangan Pernikahan {$couple} | {$siteName}"
            : "Undangan Digital | {$siteName}";

        $descriptionParts = [
            $couple !== ''
                ? "Undangan digital pernikahan {$couple}"
                : 'Undangan digital',
        ];

        if (! empty($data['akad_datetime_formatted'])) {
            $descriptionParts[] = 'akad '.$data['akad_datetime_formatted'];
        }

        if (! empty($data['akad_venue'])) {
            $descriptionParts[] = 'di '.$data['akad_venue'];
        }

        $descriptionParts[] = "dibuat dengan {$siteName}, platform undangan online modern";

        $description = implode(' — ', $descriptionParts);

        $image = $data['cover_photo_url']
            ?? $data['couple_photo_url']
            ?? $data['bride_photo_url']
            ?? $data['groom_photo_url']
            ?? SiteSetting::get('qr_logo_url')
            ?? url('/favicon.svg');

        if ($image !== null && ! str_starts_with((string) $image, 'http')) {
            $image = url($image);
        }

        $url = $invitation->getPublicUrl();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $title,
            'description' => $description,
            'url' => $url,
            'isAccessibleForFree' => true,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'organizer' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => rtrim(config('app.url'), '/'),
            ],
        ];

        if (! empty($data['akad_datetime'])) {
            $jsonLd['startDate'] = $data['akad_datetime'];
        }

        if (! empty($data['akad_venue'])) {
            $jsonLd['location'] = [
                '@type' => 'Place',
                'name' => $data['akad_venue'],
            ];
        }

        if ($image !== null) {
            $jsonLd['image'] = $image;
        }

        return [
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'image' => $image,
            'site_name' => $siteName,
            'json_ld' => $jsonLd,
        ];
    }

    /**
     * Render the HTML head tags (title, meta, Open Graph, Twitter, JSON-LD).
     *
     * @param  array<string, mixed>  $seo
     */
    public function renderHeadTags(array $seo): string
    {
        $title = e($seo['title']);
        $description = e($seo['description']);
        $url = e($seo['url']);
        $image = e($seo['image']);
        $siteName = e($seo['site_name']);

        $tags = [
            "<title>{$title}</title>",
            "<meta name=\"description\" content=\"{$description}\">",
            '<meta name="robots" content="index, follow">',
            "<link rel=\"canonical\" href=\"{$url}\">",
            '<meta property="og:type" content="website">',
            "<meta property=\"og:site_name\" content=\"{$siteName}\">",
            "<meta property=\"og:url\" content=\"{$url}\">",
            "<meta property=\"og:title\" content=\"{$title}\">",
            "<meta property=\"og:description\" content=\"{$description}\">",
            "<meta property=\"og:image\" content=\"{$image}\">",
            '<meta name="twitter:card" content="summary_large_image">',
            "<meta name=\"twitter:url\" content=\"{$url}\">",
            "<meta name=\"twitter:title\" content=\"{$title}\">",
            "<meta name=\"twitter:description\" content=\"{$description}\">",
            "<meta name=\"twitter:image\" content=\"{$image}\">",
        ];

        $tags[] = '<script type="application/ld+json">'.json_encode($seo['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>';

        return implode("\n", $tags);
    }

    /**
     * Inject SEO tags into the rendered invitation HTML head.
     *
     * @param  array<string, mixed>  $seo
     */
    public function inject(string $html, array $seo): string
    {
        $headTags = "\n    ".$this->renderHeadTags($seo);

        // Remove any existing title tag so the SEO title becomes the only one
        $html = (string) preg_replace('/<title\b[^>]*>.*?<\/title>/is', '', $html, 1);

        if (preg_match('/<head\b[^>]*>/i', $html, $match, PREG_OFFSET_CAPTURE) === 1) {
            $offset = $match[0][1] + strlen($match[0][0]);

            return substr($html, 0, $offset).$headTags.substr($html, $offset);
        }

        return $html;
    }
}
