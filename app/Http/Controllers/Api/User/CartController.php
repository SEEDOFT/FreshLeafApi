<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\CartCheckoutRequest;
use App\Http\Requests\Api\Cart\CartStoreRequest;
use App\Http\Requests\Api\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Order\OrderResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    /**
     * Get active cart rows for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $cartRows = $this->cartService
            ->getActiveCarts($user, $request->integer('per_page', 10));

        try {
            $total = $this->cartService->cartTotal($cartRows->items());
        } catch (RuntimeException) {
            abort(422, __('api.cart.insufficient_stock'));
        }

        return static::successResponse([
            'carts' => CartResource::collection($cartRows),
            'total' => $total,
        ], __('api.cart.retrieved'));
    }

    /**
     * Add an item to the cart.
     */
    public function store(CartStoreRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validated = $request->validated();

        $this->cartService->addToCart(
            $user,
            (int) $validated['vendor_inventory_id'],
            (string) $validated['quantity']
        );

        return $this->index($request);
    }

    /**
     * Update cart row quantity.
     */
    public function update(CartUpdateRequest $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validated = $request->validated();

        $this->cartService->updateCart(
            $user,
            $itemId,
            (string) $validated['quantity']
        );

        return $this->index($request);
    }

    /**
     * Mark an item as removed from the cart.
     */
    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $this->cartService->removeFromCart($user, $itemId);

        return $this->index($request);
    }

    /**
     * Convert active cart rows into an order.
     */
    public function checkout(CartCheckoutRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $validatedData = $request->validated();

        $order = $this->cartService->checkout($user, $validatedData);

        return static::successResponse(
            OrderResource::make($order),
            __('api.cart.checked_out')
        );
    }
}
