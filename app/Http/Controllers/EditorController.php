<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationContentRequest;
use App\Services\BladeRenderService;
use App\Services\CustomerInvitationService;
use App\Services\DataContractBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EditorController extends Controller
{
    public function __construct(
        protected BladeRenderService $bladeRenderer,
        protected DataContractBuilder $dataBuilder,
        protected CustomerInvitationService $customerInvitations
    ) {}

    /**
     * Show the content editor.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['template', 'content']);
        abort_if(! $invitation, 404);

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
                'bride_photo_url' => $invitation->content->bride_photo_url,
                'groom_name' => $invitation->content->groom_name,
                'groom_father' => $invitation->content->groom_father,
                'groom_mother' => $invitation->content->groom_mother,
                'groom_photo_url' => $invitation->content->groom_photo_url,
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
                'gallery_photos' => $invitation->content->gallery_photos ?? [],
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
    public function save(InvitationContentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user);
        abort_if(! $invitation, 404);

        $invitation->content()->updateOrCreate(
            ['invitation_id' => $invitation->id],
            $request->validated()
        );

        return back()->with('success', 'Konten undangan berhasil disimpan!');
    }

    /**
     * Preview invitation (authenticated user only)
     */
    public function preview(Request $request): SymfonyResponse
    {
        $user = $request->user();
        $invitation = $this->customerInvitations->activeInvitation($user, ['template', 'content', 'sections.templateSection', 'ornaments.templateOrnament', 'gallery']);
        abort_if(! $invitation, 404);

        if (! $invitation->content) {
            return response(
                '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Preview belum tersedia</title></head><body style="font-family: sans-serif; padding: 2rem; text-align: center;"><p>Konten undangan belum diisi. Silakan isi konten terlebih dahulu.</p></body></html>',
                422
            )->header('Content-Type', 'text/html; charset=utf-8');
        }

        if (! file_exists($invitation->template->getFolderPath())) {
            abort(500, 'Template files not found');
        }

        $data = $this->dataBuilder->build($invitation);
        $html = $this->bladeRenderer->renderInvitation($invitation, $data);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }
}
