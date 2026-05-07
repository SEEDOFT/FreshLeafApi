<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Wishlist\WishlistResource;
use App\Models\UserWishlistItemStatus;
use App\Models\UserWishlistItemType;
use App\Models\UserWishlistStatus;
use App\Models\UserWishlistType;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get the active wishlist for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id, 'user_wishlist_status_id' => UserWishlistStatus::ACTIVE],
            ['user_wishlist_type_id' => UserWishlistType::DEFAULT]
        );

        $wishlist->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successResponse(new WishlistResource($wishlist), __('api.wishlist.retrieved'));
    }

    /**
     * Toggle an item in the wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validated = $request->validate([
            'vendor_inventory_id' => 'required|exists:vendor_inventories,id',
        ]);

        $wishlist = Wishlist::firstOrCreate(
            ['user_id' => $user->id, 'user_wishlist_status_id' => UserWishlistStatus::ACTIVE],
            ['user_wishlist_type_id' => UserWishlistType::DEFAULT]
        );

        $wishlistItem = WishlistItem::where('user_wishlist_id', $wishlist->id)
            ->where('vendor_inventory_id', $validated['vendor_inventory_id'])
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $message = __('api.wishlist.item_removed');
        } else {
            $wishlist->items()->create([
                'vendor_inventory_id' => $validated['vendor_inventory_id'],
                'user_wishlist_item_status_id' => UserWishlistItemStatus::ACTIVE,
                'user_wishlist_item_type_id' => UserWishlistItemType::DEFAULT_TYPE ?? 1,
            ]);
            $message = __('api.wishlist.item_added');
        }

        $wishlist->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successResponse(new WishlistResource($wishlist), $message);
    }
}
