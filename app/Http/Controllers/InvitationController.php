<?php

namespace App\Http\Controllers;

use App\Services\CustomerInvitationService;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitationService,
        private CustomerInvitationService $customerInvitations
    ) {}

    /**
     * Show sections and ornaments management.
     */
    public function customize(Request $request): Response
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['template', 'sections.templateSection', 'ornaments.templateOrnament']);
        abort_if(! $invitation, 404);

        return Inertia::render('Dashboard/Customize', [
            'invitation' => [
                'id' => $invitation->id,
                'status' => $invitation->status,
                'template' => [
                    'name' => $invitation->template->name,
                    'slug' => $invitation->template->slug,
                ],
            ],
            'sections' => $invitation->sections->map(fn ($section) => [
                'id' => $section->id,
                'section_key' => $section->templateSection->file ?? 'unknown',
                'name' => $section->templateSection->label ?? 'Section',
                'is_visible' => $section->is_visible,
                'sort_order' => $section->sort_order,
            ])->sortBy('sort_order')->values(),
            'ornaments' => $invitation->ornaments->map(fn ($ornament) => [
                'id' => $ornament->id,
                'ornament_key' => $ornament->templateOrnament->file ?? 'unknown',
                'name' => $ornament->templateOrnament->label ?? 'Ornament',
                'position' => $ornament->templateOrnament->position ?? 'overlay',
                'is_active' => $ornament->is_active,
            ]),
        ]);
    }

    /**
     * Reorder sections.
     */
    public function reorderSections(Request $request): JsonResponse
    {
        $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'required|integer|exists:invitation_sections,id',
        ]);

        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $this->invitationService->reorderSections($invitation, $request->section_ids);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle section visibility.
     */
    public function toggleSection(Request $request, int $sectionId): JsonResponse
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        try {
            $isVisible = $this->invitationService->toggleSectionVisibility($invitation, $sectionId);

            return response()->json([
                'success' => true,
                'is_visible' => $isVisible,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle ornament.
     */
    public function toggleOrnament(Request $request, int $ornamentId): JsonResponse
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $isActive = $this->invitationService->toggleOrnament($invitation, $ornamentId);

        return response()->json([
            'success' => true,
            'is_active' => $isActive,
        ]);
    }

    /**
     * Publish invitation.
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
     * Unpublish invitation.
     */
    public function unpublish(Request $request)
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $this->invitationService->unpublish($invitation);

        return back()->with('success', 'Undangan berhasil di-unpublish.');
    }
}
