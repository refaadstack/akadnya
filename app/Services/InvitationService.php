<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;

class InvitationService
{
    /**
     * Reorder invitation sections.
     */
    public function reorderSections(Invitation $invitation, array $sectionIds): void
    {
        DB::transaction(function () use ($invitation, $sectionIds) {
            foreach ($sectionIds as $index => $sectionId) {
                $invitation->sections()
                    ->where('id', $sectionId)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * Toggle section visibility.
     */
    public function toggleSectionVisibility(Invitation $invitation, int $sectionId): bool
    {
        $section = $invitation->sections()->findOrFail($sectionId);

        // Prevent hiding required sections
        $requiredSections = ['hero', 'countdown', 'rsvp'];
        if (in_array($section->section_key, $requiredSections) && $section->is_visible) {
            throw new \InvalidArgumentException(
                'Section ini tidak dapat disembunyikan karena merupakan section wajib.'
            );
        }

        $section->is_visible = ! $section->is_visible;
        $section->save();

        return $section->is_visible;
    }

    /**
     * Toggle ornament.
     */
    public function toggleOrnament(Invitation $invitation, int $ornamentId): bool
    {
        $ornament = $invitation->ornaments()->findOrFail($ornamentId);

        $ornament->is_active = ! $ornament->is_active;
        $ornament->save();

        return $ornament->is_active;
    }

    /**
     * Publish invitation.
     */
    public function publish(Invitation $invitation): void
    {
        // Validate required content
        $this->validateRequiredContent($invitation);

        $invitation->status = 'published';
        $invitation->published_at = now();
        $invitation->save();

        $this->ensureAkadnyaAutoWish($invitation);
    }

    /**
     * Seed a welcome wish from Akadnya so a freshly published invitation
     * always has a greeting. Idempotent: only one platform wish per invitation.
     */
    public function ensureAkadnyaAutoWish(Invitation $invitation): void
    {
        if ($invitation->rsvps()->where('is_from_akadnya', true)->exists()) {
            return;
        }

        $content = $invitation->content;

        if (! ($content?->show_wishes ?? true)) {
            return;
        }

        $couple = trim(trim((string) ($content->groom_name ?? '')).' & '.trim((string) ($content->bride_name ?? '')));

        $sender = (string) SiteSetting::get('auto_wish_sender', 'Akadnya');

        $messageTemplate = (string) SiteSetting::get(
            'auto_wish_message',
            'Selamat menempuh hidup baru, {couple}! Semoga menjadi keluarga yang sakinah, mawaddah, warahmah, dan selalu dalam lindungan Tuhan Yang Maha Esa.'
        );

        $invitation->rsvps()->create([
            'name' => $sender,
            'attendance' => 'pending',
            'pax_count' => 0,
            'message' => str_replace('{couple}', $couple, $messageTemplate),
            'is_from_akadnya' => true,
        ]);
    }

    /**
     * Unpublish invitation.
     */
    public function unpublish(Invitation $invitation): void
    {
        $invitation->status = 'draft';
        $invitation->save();
    }

    /**
     * Validate required content before publishing.
     */
    private function validateRequiredContent(Invitation $invitation): void
    {
        $content = $invitation->content;

        if (! $content) {
            throw new \InvalidArgumentException('Konten undangan belum diisi.');
        }

        $requiredFields = [
            'bride_name' => 'Nama mempelai wanita',
            'groom_name' => 'Nama mempelai pria',
            'akad_datetime' => 'Tanggal & waktu akad',
            'akad_venue' => 'Tempat akad',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($content->$field)) {
                $missingFields[] = $label;
            }
        }

        if (! empty($missingFields)) {
            throw new \InvalidArgumentException(
                'Field berikut harus diisi: '.implode(', ', $missingFields)
            );
        }
    }
}
