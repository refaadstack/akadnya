<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\MediaUpload;
use App\Models\User;
use App\Models\UserGrant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Default storage quota in bytes (100 MB).
     */
    public const BASE_QUOTA_BYTES = 104857600;

    /**
     * Bytes granted per "extra_storage" add-on (1 GB).
     */
    public const ADDON_QUOTA_BYTES_PER_GB = 1073741824;
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
     * Upload a file for a user, enforcing the storage quota, and record
     * the upload so the used quota can be accounted for.
     */
    public function uploadFor(User $user, ?Invitation $invitation, UploadedFile $file, string $type = 'image'): string
    {
        $this->assertCanUpload($user, $file->getSize());

        $url = $this->upload($file, $type);

        MediaUpload::create([
            'user_id' => $user->id,
            'invitation_id' => $invitation?->id,
            'url' => $url,
            'size' => $file->getSize(),
            'type' => $type,
        ]);

        return $url;
    }

    /**
     * Total storage quota in bytes for a user (base + extra_storage add-ons).
     */
    public function quotaFor(User $user): int
    {
        $extraBytes = $user->features()
            ->where('feature', 'extra_storage')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->sum(fn ($feature) => (int) ($feature->metadata['storage_gb'] ?? 1) * self::ADDON_QUOTA_BYTES_PER_GB);

        if ($user->hasGrant(UserGrant::TYPE_ADDON, 'extra_storage') || $user->hasGrant(UserGrant::TYPE_ADDON)) {
            $extraBytes += self::ADDON_QUOTA_BYTES_PER_GB;
        }

        return self::BASE_QUOTA_BYTES + $extraBytes;
    }

    /**
     * Storage currently used by a user in bytes.
     */
    public function usedByUser(User $user): int
    {
        return (int) MediaUpload::where('user_id', $user->id)->sum('size');
    }

    /**
     * Throw when an upload would exceed the user's storage quota.
     */
    public function assertCanUpload(User $user, int $bytes): void
    {
        $quota = $this->quotaFor($user);
        $used = $this->usedByUser($user);

        if ($used + $bytes > $quota) {
            $usedMb = (int) round($used / 1048576);
            $quotaMb = (int) round($quota / 1048576);

            throw new \InvalidArgumentException(
                "Kuota penyimpanan tidak cukup ({$usedMb} MB dari {$quotaMb} MB terpakai). "
                .'Tambah kapasitas dengan add-on "Tambah Storage 1GB" di halaman Produk.'
            );
        }
    }

    /**
     * Remove the tracking record for an uploaded file.
     */
    public function deleteUploadRecordByUrl(string $url): void
    {
        MediaUpload::where('url', $url)->delete();
    }

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
            'background' => 'invitations/backgrounds',
            default => 'invitations/media',
        };

        // Store file
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Return full URL
        return rtrim(config('app.url'), '/').'/storage/'.$storedPath;
    }

    /**
     * Convert a stored absolute storage URL to a host-relative path so
     * images render on whatever host serves the app (dev, staging,
     * production). External URLs are returned untouched.
     */
    public static function displayUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/storage/')) {
            return $url;
        }

        return $path;
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
