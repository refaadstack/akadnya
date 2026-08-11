<?php

namespace App\Http\Controllers;

use App\Services\BladeRenderService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateAssetController extends Controller
{
    /**
     * Serve template assets from storage with path traversal protection.
     */
    public function __invoke(string $slug, string $file): BinaryFileResponse
    {
        $relativePath = str_replace('\\', '/', $file);

        if ($this->isUnsafePath($relativePath)) {
            abort(404);
        }

        $assetsPath = storage_path("app/public/templates/{$slug}/assets");
        $assetPath = $assetsPath.'/'.$relativePath;

        $realAssetsPath = realpath($assetsPath);
        $realAssetPath = realpath($assetPath);

        if (! $realAssetsPath || ! $realAssetPath || ! str_starts_with($realAssetPath, $realAssetsPath)) {
            $realAssetPath = $this->resolveSharedAsset($relativePath);
        }

        if (! $realAssetPath) {
            abort(404, 'Asset not found');
        }

        $extension = strtolower(pathinfo($realAssetPath, PATHINFO_EXTENSION));
        $mimeType = $this->mimeTypeFor($extension);

        if (! $mimeType) {
            abort(404, 'Asset type not allowed');
        }

        return response()->file($realAssetPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    protected function isUnsafePath(string $path): bool
    {
        return $path === ''
            || str_contains($path, '..')
            || str_starts_with($path, '/')
            || preg_match('/^[a-zA-Z]:/', $path) === 1;
    }

    /**
     * Resolve an asset from the shared template package, or null when absent.
     */
    protected function resolveSharedAsset(string $relativePath): ?string
    {
        $sharedPath = storage_path(BladeRenderService::SHARED_TEMPLATE_PATH.'/assets/'.$relativePath);
        $realSharedPath = realpath($sharedPath);

        if (! $realSharedPath) {
            return null;
        }

        $realSharedBase = realpath(storage_path(BladeRenderService::SHARED_TEMPLATE_PATH.'/assets'));

        if (! $realSharedBase || ! str_starts_with($realSharedPath, $realSharedBase)) {
            return null;
        }

        return $realSharedPath;
    }

    protected function mimeTypeFor(string $extension): ?string
    {
        return [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            'webm' => 'audio/webm',
        ][$extension] ?? null;
    }
}
