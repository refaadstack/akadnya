<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Invitation;
use App\Models\InvitationContent;
use App\Models\InvitationOrnament;
use App\Models\InvitationSection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use App\Models\UserFeature;
use App\Notifications\PaymentSuccessfulNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    /**
     * Create a new order for a single item (template and/or product, à la carte).
     */
    public function createOrder(
        User $user,
        ?Template $template = null,
        ?Product $product = null,
        ?array $previewData = null,
        bool $free = false
    ): Order {
        if (! $template && ! $product) {
            throw new InvalidArgumentException('An order must contain a template or a product.');
        }

        return DB::transaction(function () use ($user, $template, $product, $previewData, $free) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'total_amount' => 0, // Will be calculated below
                'metadata' => [
                    'template_slug' => $template?->slug,
                    'product_slug' => $product?->slug,
                    'preview_data' => $template ? $previewData : null,
                    'granted' => $free,
                ],
            ]);

            $totalAmount = 0;

            // Add template as order item (optional, à la carte)
            if ($template) {
                $totalAmount += $free ? 0 : $template->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => null, // Template is not a product
                    'item_type' => 'template',
                    'item_id' => $template->id,
                    'name' => $template->name,
                    'price' => $free ? 0 : $template->price,
                    'quantity' => 1,
                ]);
            }

            // Add product (optional, à la carte)
            if ($product) {
                $totalAmount += $free ? 0 : $product->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_type' => 'product',
                    'item_id' => $product->id,
                    'name' => $product->name,
                    'price' => $free ? 0 : $product->price,
                    'quantity' => 1,
                ]);
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);

            return $order->fresh(['items']);
        });
    }

    /**
     * Create an order from the user's cart items (à la carte, multi-item).
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $cartItems
     */
    public function createOrderFromCart(User $user, Collection $cartItems): Order
    {
        if ($cartItems->isEmpty()) {
            throw new InvalidArgumentException('Keranjang kosong.');
        }

        return DB::transaction(function () use ($user, $cartItems) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'total_amount' => 0,
                'metadata' => [
                    'template_slugs' => $cartItems->where('item_type', 'template')
                        ->pluck('item_id')
                        ->map(fn (int $id) => Template::find($id)?->slug)
                        ->filter()
                        ->values()
                        ->all(),
                    'product_slugs' => $cartItems->where('item_type', 'product')
                        ->pluck('item_id')
                        ->map(fn (int $id) => Product::find($id)?->slug)
                        ->filter()
                        ->values()
                        ->all(),
                    'preview_data' => $cartItems->where('item_type', 'template')
                        ->mapWithKeys(fn (CartItem $item) => [
                            Template::find($item->item_id)?->slug => $item->preview_data,
                        ])
                        ->filter(fn ($value) => $value !== null)
                        ->all(),
                ],
            ]);

            $totalAmount = 0;

            foreach ($cartItems as $cartItem) {
                $model = $cartItem->model();

                if (! $model || ! $model->is_active) {
                    throw new InvalidArgumentException('Ada item di keranjang yang tidak tersedia lagi.');
                }

                if ($cartItem->item_type === 'product' && $model instanceof Product && $model->type !== 'addon') {
                    throw new InvalidArgumentException('Ada item di keranjang yang tidak tersedia lagi.');
                }

                $totalAmount += $model->price * $cartItem->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->item_type === 'product' ? $model->id : null,
                    'item_type' => $cartItem->item_type,
                    'item_id' => $model->id,
                    'name' => $model->name,
                    'price' => $model->price,
                    'quantity' => $cartItem->quantity,
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order->fresh(['items']);
        });
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, string $status, bool $notify = true): void
    {
        $wasPaid = $order->isPaid();

        $order->update(['status' => $status]);

        // If paid, activate features
        if ($status === 'paid') {
            $this->activateFeatures($order);

            if ($notify && ! $wasPaid) {
                $order->loadMissing('user', 'items');

                try {
                    $order->user?->notify(new PaymentSuccessfulNotification($order));
                } catch (\Throwable $e) {
                    Log::warning('Payment success email could not be sent.', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Activate features after payment
     */
    protected function activateFeatures(Order $order): void
    {
        $user = $order->user;

        // Get all product order items
        $productItems = $order->items()
            ->whereNotNull('product_id')
            ->get();

        foreach ($productItems as $orderItem) {
            $product = Product::find($orderItem->product_id);

            if (! $product) {
                continue;
            }

            // Determine expiry date based on product type
            $expiresAt = null;

            if ($product->is_recurring) {
                $expiresAt = match ($product->recurring_interval) {
                    'monthly' => now()->addMonth(),
                    'yearly' => now()->addYear(),
                    default => null,
                };
            }

            // Avoid duplicate features for same order item
            UserFeature::firstOrCreate(
                ['order_item_id' => $orderItem->id],
                [
                    'user_id' => $user->id,
                    'feature' => $product->slug,
                    'activated_at' => now(),
                    'expires_at' => $expiresAt,
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'product_name' => $product->name,
                        'product_type' => $product->type,
                    ],
                ]
            );
        }

        $templateItems = $order->items()
            ->where('item_type', 'template')
            ->get();

        foreach ($templateItems as $templateItem) {
            if ($user->invitations()->where('template_id', $templateItem->item_id)->exists()) {
                continue;
            }

            $template = Template::find($templateItem->item_id);
            $previewData = $this->previewDataFor($order, $template);

            if ($template) {
                $invitation = $this->createInvitationFromOrder($user, $template, $previewData);

                if (! $user->active_invitation_id) {
                    $user->forceFill(['active_invitation_id' => $invitation->id])->save();
                }
            }
        }
    }

    /**
     * Resolve preview data for a template. Cart orders store preview data
     * keyed by template slug; legacy orders store a single flat array.
     */
    protected function previewDataFor(Order $order, ?Template $template): ?array
    {
        $previewData = $order->metadata['preview_data'] ?? null;

        if ($template && is_array($previewData) && array_key_exists($template->slug, $previewData)) {
            return $previewData[$template->slug];
        }

        return $previewData;
    }

    /**
     * Create invitation from order preview data
     */
    public function createInvitationFromOrder(User $user, Template $template, ?array $previewData): Invitation
    {
        return DB::transaction(function () use ($user, $template, $previewData) {
            // Generate unique subdomain from user name
            $baseName = $previewData['bride_name'] ?? $previewData['bride']['name'] ?? $user->name;
            $subdomain = $this->generateInvitationSubdomain($baseName);

            // Create invitation
            $invitation = Invitation::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'subdomain' => $subdomain,
                'status' => 'draft',
                'view_count' => 0,
            ]);

            // Create invitation content — populate from preview data if available
            InvitationContent::create([
                'invitation_id' => $invitation->id,
                'bride_name' => $previewData['bride_name'] ?? $previewData['bride']['name'] ?? '',
                'bride_father' => $previewData['bride_father'] ?? $previewData['bride']['father'] ?? '',
                'bride_mother' => $previewData['bride_mother'] ?? $previewData['bride']['mother'] ?? '',
                'groom_name' => $previewData['groom_name'] ?? $previewData['groom']['name'] ?? '',
                'groom_father' => $previewData['groom_father'] ?? $previewData['groom']['father'] ?? '',
                'groom_mother' => $previewData['groom_mother'] ?? $previewData['groom']['mother'] ?? '',
                'akad_datetime' => $previewData['akad_datetime'] ?? null,
                'akad_venue' => $previewData['akad_venue'] ?? $previewData['event']['location'] ?? '',
                'akad_maps_url' => $previewData['akad_maps_url'] ?? $previewData['event']['maps_url'] ?? null,
                'love_story' => $previewData['love_story'] ?? $previewData['story'] ?? null,
            ]);

            // Copy template sections to invitation sections
            $sections = $template->sections()->orderBy('sort_order')->get();
            foreach ($sections as $section) {
                InvitationSection::create([
                    'invitation_id' => $invitation->id,
                    'template_section_id' => $section->id,
                    'sort_order' => $section->sort_order,
                    'is_visible' => true,
                ]);
            }

            // Copy template ornaments to invitation ornaments
            $ornaments = $template->ornaments()->get();
            foreach ($ornaments as $ornament) {
                InvitationOrnament::create([
                    'invitation_id' => $invitation->id,
                    'template_ornament_id' => $ornament->id,
                    'is_active' => $ornament->default_active,
                ]);
            }

            return $invitation;
        });
    }

    /**
     * Generate unique invitation subdomain
     */
    protected function generateInvitationSubdomain(string $name): string
    {
        $baseSubdomain = Str::slug($name);
        $subdomain = $baseSubdomain;
        $counter = 1;

        while (Invitation::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain.'-'.$counter;
            $counter++;
        }

        return $subdomain;
    }
}
