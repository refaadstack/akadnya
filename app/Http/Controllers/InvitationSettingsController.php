<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvitationSettingsController extends Controller
{
    /**
     * Show invitation settings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->with('content')->firstOrFail();

        return Inertia::render('Dashboard/Settings', [
            'invitation' => [
                'id' => $invitation->id,
                'subdomain' => $invitation->subdomain,
                'custom_domain' => $invitation->custom_domain,
                'status' => $invitation->status,
                'view_count' => $invitation->view_count,
                'public_url' => $invitation->getPublicUrl(),
                'is_published' => $invitation->status === 'published',
            ],
        ]);
    }

    /**
     * Update subdomain
     */
    public function updateSubdomain(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                'unique:invitations,subdomain,'.$invitation->id,
            ],
        ], [
            'subdomain.regex' => 'Subdomain hanya boleh mengandung huruf kecil, angka, dan tanda hubung (-)',
            'subdomain.unique' => 'Subdomain sudah digunakan, pilih yang lain',
        ]);

        $invitation->update(['subdomain' => $validated['subdomain']]);

        return back()->with('success', 'Subdomain berhasil diubah!');
    }

    /**
     * Update custom domain
     */
    public function updateCustomDomain(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $validated = $request->validate([
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/i',
                'unique:invitations,custom_domain,'.$invitation->id,
            ],
        ], [
            'custom_domain.regex' => 'Format domain tidak valid (contoh: undangan.example.com)',
            'custom_domain.unique' => 'Domain sudah digunakan',
        ]);

        $invitation->update(['custom_domain' => $validated['custom_domain']]);

        return back()->with('success', 'Custom domain berhasil diubah!');
    }

    /**
     * Publish invitation
     */
    public function publish(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->with('content')->firstOrFail();

        // Validate required content
        if (! $invitation->content) {
            return back()->withErrors(['error' => 'Harap isi konten undangan terlebih dahulu']);
        }

        if (! $invitation->content->bride_name || ! $invitation->content->groom_name) {
            return back()->withErrors(['error' => 'Nama mempelai wajib diisi']);
        }

        if (! $invitation->content->akad_datetime || ! $invitation->content->akad_venue) {
            return back()->withErrors(['error' => 'Informasi akad nikah wajib diisi']);
        }

        $invitation->update(['status' => 'published']);

        return back()->with('success', 'Undangan berhasil dipublikasikan! 🎉');
    }

    /**
     * Unpublish invitation
     */
    public function unpublish(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $invitation->update(['status' => 'draft']);

        return back()->with('success', 'Undangan berhasil di-unpublish');
    }

    /**
     * Generate random subdomain
     */
    public function generateSubdomain(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        // Generate random subdomain
        do {
            $subdomain = Str::slug(Str::random(8));
        } while (Invitation::where('subdomain', $subdomain)->exists());

        return response()->json([
            'subdomain' => $subdomain,
        ]);
    }
}
