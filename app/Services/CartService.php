<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CartService
{
    public const MAX_QUANTITY = 10;

    /**
     * Add an item to the user cart. Templates always have quantity 1;
     * adding the same product again increments its quantity.
     */
    public function add(User $user, string $itemType, int $itemId, ?array $previewData = null): CartItem
    {
        $this->assertPurchasable($itemType, $itemId);

        $item = CartItem::firstOrNew([
            "user_id" => $user->id,
            "item_type" => $itemType,
            "item_id" => $itemId,
        ]);

        if ($item->exists) {
            if ($itemType === "template") {
                $item->preview_data = $previewData;
            } else {
                $item->quantity = min($item->quantity + 1, self::MAX_QUANTITY);
            }
        } else {
            $item->quantity = 1;
            $item->preview_data = $itemType === "template" ? $previewData : null;
        }

        $item->save();

        return $item;
    }

    public function updateQuantity(User $user, int $cartItemId, int $quantity): CartItem
    {
        $item = $this->findOwned($user, $cartItemId);

        if ($item->item_type === "template") {
            throw new InvalidArgumentException("Template quantity cannot be changed.");
        }

        $item->quantity = max(1, min($quantity, self::MAX_QUANTITY));
        $item->save();

        return $item;
    }

    public function remove(User $user, int $cartItemId): void
    {
        $this->findOwned($user, $cartItemId)->delete();
    }

    public function clear(User $user): void
    {
        CartItem::forUser($user)->delete();
    }

    public function count(User $user): int
    {
        return (int) CartItem::forUser($user)->sum("quantity");
    }

    /**
     * Cart items enriched with their template/product data, ready for Inertia.
     *
     * @return array{items: Collection, totals: array<string, mixed>}
     */
    public function forPage(User $user): array
    {
        $cartItems = CartItem::forUser($user)
            ->orderBy('id')
            ->get();

        $templateIds = $cartItems->where("item_type", "template")->pluck("item_id");
        $productIds = $cartItems->where("item_type", "product")->pluck("item_id");

        $templates = $templateIds->isEmpty()
            ? collect()
            : Template::whereIn("id", $templateIds)->get()->keyBy("id");
        $products = $productIds->isEmpty()
            ? collect()
            : Product::whereIn("id", $productIds)->get()->keyBy("id");

        $items = $cartItems
            ->map(function (CartItem $cartItem) use ($templates, $products): ?array {
                if ($cartItem->item_type === "template") {
                    $model = $templates->get($cartItem->item_id);
                } else {
                    $model = $products->get($cartItem->item_id);
                }

                if (! $model) {
                    return null;
                }

                return [
                    "id" => $cartItem->id,
                    "type" => $cartItem->item_type,
                    "item_id" => $model->id,
                    "slug" => $model->slug,
                    "name" => $model->name,
                    "description" => $cartItem->item_type === "template"
                        ? "Template undangan digital"
                        : $model->description,
                    "price" => $model->price,
                    "original_price" => $model->original_price,
                    "discount_percent" => $model->discount_percent,
                    "quantity" => $cartItem->quantity,
                    "is_free" => $cartItem->item_type === "template" ? $model->is_free : false,
                ];
            })
            ->filter()
            ->values();

        $subtotal = $items->sum(fn (array $item): float => (float) $item["price"] * $item["quantity"]);
        $originalSubtotal = $items->sum(fn (array $item): float => $item["original_price"]
            ? (float) $item["original_price"] * $item["quantity"]
            : 0.0);

        return [
            "items" => $items,
            "totals" => [
                "item_count" => $items->count(),
                "subtotal" => $subtotal,
                "original_subtotal" => $originalSubtotal > $subtotal ? $originalSubtotal : null,
                "savings" => $originalSubtotal > $subtotal ? $originalSubtotal - $subtotal : 0,
            ],
        ];
    }

    /**
     * Ensure a guest-facing item is currently purchasable.
     */
    protected function assertPurchasable(string $itemType, int $itemId): void
    {
        if ($itemType === "template") {
            $purchasable = Template::where("id", $itemId)->where("is_active", true)->exists();
        } elseif ($itemType === "product") {
            $purchasable = Product::where("id", $itemId)
                ->where("type", "addon")
                ->where("is_active", true)
                ->exists();
        } else {
            $purchasable = false;
        }

        if (! $purchasable) {
            throw new InvalidArgumentException("Item tidak tersedia.");
        }
    }

    protected function findOwned(User $user, int $cartItemId): CartItem
    {
        return CartItem::forUser($user)->findOrFail($cartItemId);
    }
}
