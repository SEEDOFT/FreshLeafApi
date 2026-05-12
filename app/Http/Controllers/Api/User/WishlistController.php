<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Wishlist\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    private const array RELATIONSHIP = [
        'items.vendorInventory.product',
        'items.vendorInventory.vendor',
        'items.vendorInventory.unit',
        'items.status',
        'items.type',
        'status',
        'type',
    ];

    /**
     * Get the active wishlist for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'user_wishlist_status_id' => WishlistStatus::ACTIVE_ID,
        ]);

        $wishlist->load(self::RELATIONSHIP);

        return static::successResponse(
            new WishlistResource($wishlist),
            __('api.wishlist.retrieved')
        );
    }

    /**
     * Toggle an item in the wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'vendor_inventory_id' => 'required|exists:vendor_inventories,id',
        ]);

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'vendor_inventory_id' => $validatedData['vendor_inventory_id'],
            'user_wishlist_status_id' => WishlistStatus::ACTIVE_ID,
        ]);

        $wishlist->load(self::RELATIONSHIP);

        return static::successResponse(new WishlistResource($wishlist));
    }
}
