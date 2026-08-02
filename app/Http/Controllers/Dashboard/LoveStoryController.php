<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\CustomerInvitationService;
use Illuminate\Http\RedirectResponse;
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
        $invitation = $this->customerInvitations->activeInvitation($user, ['content', 'loveStories']);

        if (! $invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $content = $invitation->content;

        return Inertia::render('Dashboard/LoveStory/Index', [
            'love_story' => $content?->love_story ?? '',
            'special_message' => $content?->special_message ?? '',
            'stories' => $invitation->loveStories->map(fn ($story) => [
                'id' => $story->id,
                'title' => $story->title,
                'date_label' => $story->date_label,
                'description' => $story->description,
                'sort_order' => $story->sort_order,
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['loveStories']);

        if (! $invitation) {
            abort(404, 'Tidak ada undangan aktif');
        }

        $validated = $request->validate([
            'love_story' => 'nullable|string',
            'special_message' => 'nullable|string',
            'stories' => 'nullable|array',
            'stories.*.id' => 'nullable|integer',
            'stories.*.title' => 'required|string|max:255',
            'stories.*.date_label' => 'nullable|string|max:255',
            'stories.*.description' => 'nullable|string',
            'stories.*.sort_order' => 'required|integer|min:0',
        ]);

        $invitation->content()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            ['love_story' => $validated['love_story'] ?? null, 'special_message' => $validated['special_message'] ?? null]
        );

        $submittedIds = [];
        foreach ($validated['stories'] ?? [] as $story) {
            if (! empty($story['id'])) {
                $model = $invitation->loveStories->firstWhere('id', $story['id']);
            }

            if (isset($model)) {
                $model->update([
                    'title' => $story['title'],
                    'date_label' => $story['date_label'] ?? null,
                    'description' => $story['description'] ?? null,
                    'sort_order' => $story['sort_order'],
                ]);
            } else {
                $model = $invitation->loveStories()->create([
                    'title' => $story['title'],
                    'date_label' => $story['date_label'] ?? null,
                    'description' => $story['description'] ?? null,
                    'sort_order' => $story['sort_order'],
                ]);
            }

            $submittedIds[] = $model->id;
            unset($model);
        }

        $invitation->loveStories()->whereNotIn('id', $submittedIds)->delete();

        return back()->with('success', 'Love story berhasil disimpan!');
    }
}
