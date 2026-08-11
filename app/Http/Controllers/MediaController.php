<?php

namespace App\Http\Controllers;

use App\Services\CustomerInvitationService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService,
        private CustomerInvitationService $customerInvitations
    ) {}

    /**
     * Upload cover photo.
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'cover');
    }

    /**
     * Upload gallery photo.
     */
    public function uploadGallery(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'gallery');
    }

    /**
     * Upload music file.
     */
    public function uploadMusic(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        return $this->mediaUpload($request, 'music');
    }

    /**
     * Upload QRIS image.
     */
    public function uploadQris(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:2048', // 2MB max
        ]);

        return $this->mediaUpload($request, 'qris');
    }

    /**
     * Upload bride photo.
     */
    public function uploadBridePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'bride');
    }

    /**
     * Upload groom photo.
     */
    public function uploadGroomPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'groom');
    }

    /**
     * Upload couple photo.
     */
    public function uploadCouplePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'couple');
    }

    /**
     * Upload page background image.
     */
    public function uploadBackground(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
        ]);

        return $this->mediaUpload($request, 'background');
    }

    /**
     * Upload the validated file for the user's active invitation.
     */
    private function mediaUpload(Request $request, string $type): JsonResponse
    {
        try {
            $invitation = $this->customerInvitations->activeInvitation($request->user());
            $url = $this->mediaService->uploadFor($request->user(), $invitation, $request->file('file'), $type);

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