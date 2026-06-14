<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rating\VendorInventoryRatingResource;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\VendorInventory;
use App\Models\VendorInventoryRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorInventoryRatingController extends Controller
{
    public function forVendorInventory(Request $request, int $id): JsonResponse
    {
        $inventory = VendorInventory::with('ratings.user')->findOrFail($id);

        $ratings = $inventory->ratings()
            ->with('user')
            ->latest()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse([
            'average_rating' => round((float) $inventory->ratings->avg('rating'), 1),
            'ratings_count' => $inventory->ratings->count(),
            'ratings' => VendorInventoryRatingResource::collection($ratings),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validated = $request->validate([
            'order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $orderItem = OrderItem::where('id', $validated['order_item_id'])
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('order_status_id', OrderStatus::DELIVERED_ID);
            })
            ->firstOrFail();

        $rating = VendorInventoryRating::updateOrCreate(
            [
                'user_id' => $user->id,
                'vendor_inventory_id' => $orderItem->vendor_inventory_id,
            ],
            [
                'order_item_id' => $validated['order_item_id'],
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ],
        );

        return static::successResponse(
            ['rating' => new VendorInventoryRatingResource($rating->load('user'))],
            __('api.rating.created'),
        );
    }

    public function userRatings(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $ratings = VendorInventoryRating::where('user_id', $user->id)
            ->with('vendorInventory.product', 'user')
            ->latest()
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse([
            'ratings' => VendorInventoryRatingResource::collection($ratings),
        ]);
    }
}
