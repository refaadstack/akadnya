<?php

namespace App\Http\Controllers;

use App\Models\InvitationGallery;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Display gallery management page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->with('gallery')->firstOrFail();

        return Inertia::render('Dashboard/Gallery', [
            'gallery' => $invitation->gallery->map(fn ($photo) => [
                'id' => $photo->id,
                'image_url' => $photo->image_url,
                'caption' => $photo->caption,
                'sort_order' => $photo->sort_order,
            ])->sortBy('sort_order')->values(),
        ]);
    }

    /**
     * Upload new photo
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $request->validate([
            'file' => 'required|image|max:5120', // 5MB
            'caption' => 'nullable|string|max:255',
        ]);

        try {
            $url = $this->mediaService->upload($request->file('file'), 'gallery');

            // Get next sort order
            $maxOrder = $invitation->gallery()->max('sort_order') ?? 0;

            $photo = $invitation->gallery()->create([
                'image_url' => $url,
                'caption' => $request->caption,
                'sort_order' => $maxOrder + 1,
            ]);

            return back()->with('success', 'Foto berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal upload foto: '.$e->getMessage()]);
        }
    }

    /**
     * Update photo caption
     */
    public function update(Request $request, InvitationGallery $photo)
    {
        $user = $request->user();

        // Ensure photo belongs to user's invitation
        if ($photo->invitation->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
        ]);

        $photo->update($validated);

        return back()->with('success', 'Caption berhasil diupdate!');
    }

    /**
     * Delete photo
     */
    public function destroy(Request $request, InvitationGallery $photo)
    {
        $user = $request->user();

        // Ensure photo belongs to user's invitation
        if ($photo->invitation->user_id !== $user->id) {
            abort(403);
        }

        // Delete file from storage
        $path = str_replace('/storage/', '', $photo->image_url);
        \Storage::disk('public')->delete($path);

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus!');
    }

    /**
     * Reorder photos
     */
    public function reorder(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $validated = $request->validate([
            'photos' => 'required|array',
            'photos.*.id' => 'required|exists:invitation_galleries,id',
            'photos.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['photos'] as $photoData) {
            $photo = InvitationGallery::find($photoData['id']);

            // Ensure photo belongs to user's invitation
            if ($photo->invitation_id === $invitation->id) {
                $photo->update(['sort_order' => $photoData['sort_order']]);
            }
        }

        return back()->with('success', 'Urutan foto berhasil diubah!');
    }
}
