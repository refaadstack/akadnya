<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationContentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditorController extends Controller
{
    /**
     * Show the content editor.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $invitation = $user->invitations()->with(['template', 'content'])->firstOrFail();

        return Inertia::render('Dashboard/Editor', [
            'invitation' => [
                'id' => $invitation->id,
                'status' => $invitation->status,
                'template' => [
                    'name' => $invitation->template->name,
                    'slug' => $invitation->template->slug,
                ],
            ],
            'content' => $invitation->content ? [
                'bride_name' => $invitation->content->bride_name,
                'bride_father' => $invitation->content->bride_father,
                'bride_mother' => $invitation->content->bride_mother,
                'groom_name' => $invitation->content->groom_name,
                'groom_father' => $invitation->content->groom_father,
                'groom_mother' => $invitation->content->groom_mother,
                'akad_datetime' => $invitation->content->akad_datetime?->format('Y-m-d\TH:i'),
                'akad_venue' => $invitation->content->akad_venue,
                'akad_maps_url' => $invitation->content->akad_maps_url,
                'reception_datetime' => $invitation->content->reception_datetime?->format('Y-m-d\TH:i'),
                'reception_venue' => $invitation->content->reception_venue,
                'reception_maps_url' => $invitation->content->reception_maps_url,
                'love_story' => $invitation->content->love_story,
                'special_message' => $invitation->content->special_message,
                'cover_photo_url' => $invitation->content->cover_photo_url,
                'music_url' => $invitation->content->music_url,
                'bank_name' => $invitation->content->bank_name,
                'account_number' => $invitation->content->account_number,
                'account_name' => $invitation->content->account_name,
                'qris_image_url' => $invitation->content->qris_image_url,
                'gopay_number' => $invitation->content->gopay_number,
                'ovo_number' => $invitation->content->ovo_number,
                'dana_number' => $invitation->content->dana_number,
            ] : null,
        ]);
    }

    /**
     * Save invitation content.
     */
    public function save(InvitationContentRequest $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()->firstOrFail();

        $invitation->content()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            $request->validated()
        );

        return back()->with('success', 'Konten undangan berhasil disimpan!');
    }

    /**
     * Preview invitation (authenticated user only)
     */
    public function preview(Request $request)
    {
        $user = $request->user();
        $invitation = $user->invitations()
            ->with(['template', 'content', 'sections.templateSection', 'ornaments.templateOrnament', 'gallery'])
            ->firstOrFail();

        // Use same structure as public invitation but for authenticated preview
        $content = $invitation->content;

        if (! $content) {
            return Inertia::render('Dashboard/EditorPreview', [
                'error' => 'Konten undangan belum diisi. Silakan isi konten terlebih dahulu.',
            ]);
        }

        return Inertia::render('Dashboard/EditorPreview', [
            'invitation' => [
                'id' => $invitation->id,
                'subdomain' => $invitation->subdomain,
                'status' => $invitation->status,
                'template' => [
                    'name' => $invitation->template->name,
                    'slug' => $invitation->template->slug,
                ],
                'content' => [
                    'bride_name' => $content->bride_name,
                    'bride_father' => $content->bride_father,
                    'bride_mother' => $content->bride_mother,
                    'groom_name' => $content->groom_name,
                    'groom_father' => $content->groom_father,
                    'groom_mother' => $content->groom_mother,
                    'akad_datetime' => $content->akad_datetime,
                    'akad_venue' => $content->akad_venue,
                    'akad_maps_url' => $content->akad_maps_url,
                    'reception_datetime' => $content->reception_datetime,
                    'reception_venue' => $content->reception_venue,
                    'reception_maps_url' => $content->reception_maps_url,
                    'cover_photo_url' => $content->cover_photo_url,
                    'music_url' => $content->music_url,
                    'love_story' => $content->love_story,
                    'special_message' => $content->special_message,
                    'bank_name' => $content->bank_name,
                    'account_number' => $content->account_number,
                    'account_name' => $content->account_name,
                    'qris_image_url' => $content->qris_image_url,
                    'gopay_number' => $content->gopay_number,
                    'ovo_number' => $content->ovo_number,
                    'dana_number' => $content->dana_number,
                ],
                'sections' => $invitation->sections()
                    ->where('is_visible', true)
                    ->with('templateSection')
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn ($section) => [
                        'id' => $section->id,
                        'file' => $section->templateSection->file,
                        'label' => $section->templateSection->label,
                        'sort_order' => $section->sort_order,
                    ]),
                'ornaments' => $invitation->ornaments()
                    ->where('is_active', true)
                    ->with('templateOrnament')
                    ->get()
                    ->map(fn ($ornament) => [
                        'id' => $ornament->id,
                        'file' => $ornament->templateOrnament->file,
                        'label' => $ornament->templateOrnament->label,
                        'position' => $ornament->templateOrnament->position,
                    ]),
                'gallery' => $invitation->gallery->map(fn ($photo) => [
                    'id' => $photo->id,
                    'image_url' => $photo->image_url,
                    'caption' => $photo->caption,
                ]),
            ],
        ]);
    }
}
