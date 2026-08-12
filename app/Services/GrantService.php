<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Template;
use App\Models\User;
use App\Services\OrderService;

class GrantService
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Activate a template for a granted user: creates a free paid order
     * and an invitation without going through payment.
     */
    public function activateTemplate(User $user, Template $template, ?array $previewData = null): Invitation
    {
        abort_unless($user->hasTemplateAccess($template), 403, 'Anda tidak memiliki akses ke template ini.');

        $existing = $user->invitations()->where('template_id', $template->id)->first();

        if ($existing) {
            return $existing;
        }

        $order = $this->orderService->createOrder($user, $template, null, $previewData, free: true);
        $this->orderService->updateOrderStatus($order, 'paid', notify: false);

        $invitation = $user->invitations()->where('template_id', $template->id)->firstOrFail();

        $user->forceFill(['active_invitation_id' => $invitation->id])->save();

        return $invitation;
    }
}
