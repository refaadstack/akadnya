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
        'audio/x-mp3',
        'audio/x-mpeg',
        'audio/mpeg3',
        'audio/wav',
        'audio/x-wav',
        'audio/x-wave',
        'audio/wave',
        'audio/vnd.wave',
        'audio/ogg',
        'audio/x-ogg',
        'application/ogg',
        'audio/mp4',
        'audio/x-m4a',
        'audio/aac',
        'audio/flac',
        'audio/x-flac',
    ];

    /**
     * Audio extensions accepted as a fallback when finfo reports an
     * ambiguous MIME type (e.g. application/octet-stream for some MP3s,
     * or video/mp4 for M4A files).
     */
    private const ALLOWED_AUDIO_EXTENSIONS = [
        'mp3',
        'wav',
        'ogg',
        'oga',
        'm4a',
        'aac',
        'flac',
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
            'couple' => 'invitations/couple',
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

        // finfo can misdetect real-world audio (odd ID3 tags, VBR frames,
        // M4A boxes, etc.) as non-audio MIME types. For music uploads, also
        // accept files whose extension is a known audio extension so valid
        // songs are never blocked.
        $hasAllowedAudioExtension = $type === 'music'
            && in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_AUDIO_EXTENSIONS);

        if (! in_array($mimeType, $allowedMimes) && ! $hasAllowedAudioExtension) {
            throw new \InvalidArgumentException(
                "File type not allowed. Detected MIME type: {$mimeType}. "
                .'Supported: MP3, WAV, OGG, M4A, AAC, FLAC'
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
