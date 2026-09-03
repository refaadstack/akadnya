<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\OrderItem;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Collection;

class CustomerInvitationService
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * @return Collection<int, Invitation>
     */
    public function ownedInvitations(User $user): Collection
    {
        $this->ensurePaidTemplateInvitations($user);

        return $user->invitations()
            ->with('template')
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<int, string>  $with
     */
    public function activeInvitation(User $user, array $with = []): ?Invitation
    {
        $this->ensurePaidTemplateInvitations($user);

        return $user->currentInvitation($with);
    }

    public function selectInvitation(User $user, Invitation $invitation): void
    {
        abort_unless($invitation->user_id === $user->id, 403);

        $user->forceFill(['active_invitation_id' => $invitation->id])->save();
    }

    /**
     * Adopt any active template: create an invitation from it if the user does
     * not already own one, then set it as the active invitation.
     */
    public function adoptTemplate(User $user, Template $template, ?array $previewData = null): Invitation
    {
        $existing = $user->invitations()->where('template_id', $template->id)->first();

        if ($existing) {
            $user->forceFill(['active_invitation_id' => $existing->id])->save();

            return $existing;
        }

        $invitation = $this->orderService->createInvitationFromOrder($user, $template, $previewData);

        $user->forceFill(['active_invitation_id' => $invitation->id])->save();

        return $invitation;
    }

    private function ensurePaidTemplateInvitations(User $user): void
    {
        $templateItems = OrderItem::query()
            ->with('order')
            ->where('item_type', 'template')
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('status', 'paid'))
            ->get();

        foreach ($templateItems as $item) {
            if ($user->invitations()->where('template_id', $item->item_id)->exists()) {
                continue;
            }

            $template = Template::active()->find($item->item_id);

            if (! $template) {
                continue;
            }

            $previewData = $item->order?->metadata['preview_data'] ?? null;
            $invitation = $this->orderService->createInvitationFromOrder($user, $template, $previewData);

            if (! $user->active_invitation_id) {
                $user->forceFill(['active_invitation_id' => $invitation->id])->save();
            }
        }
    }
}
