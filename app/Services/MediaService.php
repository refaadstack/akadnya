<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Allowed MIME types for uploads.
     */
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    private const ALLOWED_AUDIO_MIMES = [
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/ogg',
    ];

    /**
     * Upload a file and return its URL.
     */
    public function upload(UploadedFile $file, string $type = 'image'): string
    {
        // Validate MIME type using finfo
        $this->validateMimeType($file, $type);

        // Generate UUID filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Determine storage path based on type
        $path = match ($type) {
            'cover' => 'invitations/covers',
            'gallery' => 'invitations/gallery',
            'music' => 'invitations/music',
            default => 'invitations/media',
        };

        // Store file
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Return full URL
        return rtrim(config('app.url'), '/').'/storage/'.$storedPath;
    }

    /**
     * Validate file MIME type using finfo.
     */
    private function validateMimeType(UploadedFile $file, string $type): void
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $allowedMimes = match ($type) {
            'music' => self::ALLOWED_AUDIO_MIMES,
            default => self::ALLOWED_IMAGE_MIMES,
        };

        if (! in_array($mimeType, $allowedMimes)) {
            throw new \InvalidArgumentException(
                "File type not allowed. Detected MIME type: {$mimeType}"
            );
        }
    }

    /**
     * Delete a file from storage.
     */
    public function delete(string $url): bool
    {
        // Extract path from URL
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));

        return Storage::disk('public')->delete($path);
    }
}
