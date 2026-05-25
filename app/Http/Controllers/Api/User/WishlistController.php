<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Wishlist\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistHistory;
use App\Models\WishlistStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WishlistController extends Controller
{
    /**
     * @var list<string>
     */
    private const array RELATIONSHIP = [
        'vendorInventory.product.productCategory',
        'vendorInventory.product.type',
        'vendorInventory.product.defaultUnit',
        'vendorInventory.product.status',
        'vendorInventory.packagingType',
        'vendorInventory.unit',
        'vendorInventory.currency',
        'vendorInventory.vendor',
        'vendorInventory.status',
        'vendorInventory.activeDiscount',
        'status',
    ];

    /**
     * Get active wishlist items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $items = Wishlist::active($user->id)
            ->with(self::RELATIONSHIP)
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse(
            ['wishlists' => WishlistResource::collection($items)],
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
            'vendor_inventory_id' => ['required', 'integer', 'exists:vendor_inventories,id'],
        ]);

        $wishlist = Wishlist::withTrashed()
            ->where('user_id', $user->id)
            ->where('vendor_inventory_id', $validatedData['vendor_inventory_id'])
            ->first();

        if ($wishlist instanceof Wishlist && $wishlist->isActive()) {
            // Toggle OFF: soft-delete the existing active record
            $wishlist->update([
                'wishlist_status_id' => WishlistStatus::DELETED_ID,
                'deleted_at' => Carbon::now(),
            ]);

            $wishlist->refresh();

            WishlistHistory::insert([
                'user_id' => $user->id,
                'wishlist_id' => $wishlist->id,
                'vendor_inventory_id' => $wishlist->vendor_inventory_id,
                'wishlist_status_id' => $wishlist->wishlist_status_id,
                'created_at' => $wishlist->created_at,
                'updated_at' => $wishlist->updated_at,
                'deleted_at' => $wishlist->deleted_at,
            ]);

            $message = __('api.wishlist.item_removed');
        } else {
            // Toggle ON: resurrect a soft-deleted record, or create a fresh one
            $wishlist = Wishlist::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'vendor_inventory_id' => $validatedData['vendor_inventory_id'],
                ],
                [
                    'wishlist_status_id' => WishlistStatus::ACTIVE_ID,
                    'deleted_at' => null,
                ]
            );

            WishlistHistory::insert([
                'user_id' => $user->id,
                'wishlist_id' => $wishlist->id,
                'vendor_inventory_id' => $wishlist->vendor_inventory_id,
                'wishlist_status_id' => $wishlist->wishlist_status_id,
                'created_at' => $wishlist->created_at,
                'updated_at' => $wishlist->updated_at,
            ]);

            $message = __('api.wishlist.item_added');
        }

        return static::successResponse(
            ['wishlists' => WishlistResource::collection(
                Wishlist::active($user->id)
                    ->with(self::RELATIONSHIP)
                    ->simplePaginate($request->integer('per_page', 10))
            )],
            $message
        );
    }
}
