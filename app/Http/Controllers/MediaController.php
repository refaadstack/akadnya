<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Upload cover photo.
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'cover');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload gallery photo.
     */
    public function uploadGallery(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'gallery');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload music file.
     */
    public function uploadMusic(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'music');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload QRIS image.
     */
    public function uploadQris(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:2048', // 2MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'qris');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload bride photo.
     */
    public function uploadBridePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'bride');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload groom photo.
     */
    public function uploadGroomPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'groom');

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
