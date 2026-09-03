<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationContent;

/**
 * Generates a landscape Open Graph image (1200x630) for public invitations.
 *
 * WhatsApp and most social crawlers prefer a 1.91:1 (1200x630) image that is
 * well under 300KB. Couple cover photos are usually high-resolution portraits
 * that get rejected as previews, so we produce a trimmed, compressed landscape
 * copy and cache it in storage.
 */
class OgImageService
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    private const QUALITY = 80;

    private const CACHE_DIR = 'og/invitations';

    /**
     * Generate (or reuse) the OG landscape image for an invitation.
     *
     * Returns the absolute URL of the generated image, or null when no usable
     * source photo or GD is available so callers can fall back gracefully.
     */
    public function forInvitation(Invitation $invitation): ?string
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $source = $this->resolveSourceImage($invitation);

        if ($source === null) {
            return null;
        }

        $path = $this->ensureOgImage($source, (int) $invitation->id);

        if ($path === null) {
            return null;
        }

        return $this->publicUrl($path);
    }

    /**
     * Best available source image disk path from the invitation content,
     * falling back to the template thumbnail (which is already landscape).
     */
    private function resolveSourceImage(Invitation $invitation): ?string
    {
        $content = $invitation->content;

        foreach ($this->photoCandidates($content) as $url) {
            $path = $this->resolveStoragePath($url);

            if ($path !== null && is_file($path)) {
                return $path;
            }
        }

        $templateThumbnail = $invitation->template?->thumbnail_url;

        if (! empty($templateThumbnail)) {
            $path = $this->resolveStoragePath($templateThumbnail);

            if ($path !== null && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<?string>
     */
    private function photoCandidates(?InvitationContent $content): array
    {
        return [
            $content?->cover_photo_url,
            $content?->couple_photo_url,
            $content?->bride_photo_url,
            $content?->groom_photo_url,
        ];
    }

    /**
     * Map a public/absolute storage URL back to its disk path.
     */
    private function resolveStoragePath(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Strip any query string (the thumbnail accessor appends ?v=mtime).
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (! str_starts_with($path, '/templates/')) {
            return null;
        }

        $relative = rawurldecode(ltrim($path, '/'));

        if (str_contains($relative, '..')) {
            return null;
        }

        $fullPath = storage_path('app/public/'.$relative);

        return is_file($fullPath) ? $fullPath : null;
    }

    /**
     * Generate the landscape OG image, cache it, and return its disk path.
     */
    private function ensureOgImage(string $source, int $invitationId): ?string
    {
        $key = md5($source.'|'.(string) @filemtime($source));
        $dir = storage_path('app/public/'.self::CACHE_DIR);
        $filename = "{$invitationId}_{$key}.jpg";
        $output = $dir.'/'.$filename;

        if (is_file($output)) {
            return $output;
        }

        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return null;
        }

        $src = $this->loadImage($source);

        if ($src === null) {
            return null;
        }

        $dst = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        if ($dst === false) {
            imagedestroy($src);

            return null;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        $scale = max(self::WIDTH / $srcW, self::HEIGHT / $srcH);
        $cropW = (int) round(self::WIDTH / $scale);
        $cropH = (int) round(self::HEIGHT / $scale);
        $cropX = (int) round(($srcW - $cropW) / 2);
        $cropY = (int) round(($srcH - $cropH) / 2);

        imagecopyresampled(
            $dst,
            $src,
            0,
            0,
            max(0, $cropX),
            max(0, $cropY),
            self::WIDTH,
            self::HEIGHT,
            (int) $cropW,
            (int) $cropH
        );

        imagedestroy($src);

        $saved = imagejpeg($dst, $output, self::QUALITY);
        imagedestroy($dst);

        return $saved ? $output : null;
    }

    private function loadImage(string $path): ?\GdImage
    {
        $mime = @mime_content_type($path);

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => false,
        };

        return $image ?: null;
    }

    private function publicUrl(string $path): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $mtime = (string) @filemtime($path);
        $relative = 'storage/'.self::CACHE_DIR.'/'.basename($path);

        return $base.'/'.$relative.'?v='.$mtime;
    }
}
