<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\CustomerInvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoveStoryController extends Controller
{
    public function __construct(
        private CustomerInvitationService $customerInvitations
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['content']);
        
        if (!$invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $content = $invitation->content;

        return Inertia::render('Dashboard/LoveStory/Index', [
            'love_story' => $content?->love_story ?? '',
            'special_message' => $content?->special_message ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        
        if (!$invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $validated = $request->validate([
            'love_story' => 'nullable|string',
            'special_message' => 'nullable|string',
        ]);

        $invitation->content()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            $validated
        );

        return back()->with('success', 'Love story berhasil disimpan!');
    }
}
