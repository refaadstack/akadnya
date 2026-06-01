<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\CustomerInvitationService;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvitationSettingsController extends Controller
{
    public function __construct(
        private CustomerInvitationService $customerInvitations,
        private InvitationService $invitationService
    ) {}

    /**
     * Show invitation settings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['content']);
        abort_if(! $invitation, 404);

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
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

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
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

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
        $invitation = $this->customerInvitations->activeInvitation($user, ['content']);
        abort_if(! $invitation, 404);

        try {
            $this->invitationService->publish($invitation);

            return back()->with('success', 'Undangan berhasil dipublikasikan! 🎉');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish invitation
     */
    public function unpublish(Request $request)
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $this->invitationService->unpublish($invitation);

        return back()->with('success', 'Undangan berhasil di-unpublish');
    }

    /**
     * Generate random subdomain
     */
    public function generateSubdomain(Request $request)
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        // Generate random subdomain
        do {
            $subdomain = Str::slug(Str::random(8));
        } while (Invitation::where('subdomain', $subdomain)->exists());

        return response()->json([
            'subdomain' => $subdomain,
        ]);
    }
}
