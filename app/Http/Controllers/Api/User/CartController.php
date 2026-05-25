<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderType;
use App\Models\PaymentStatus;
use App\Models\VendorInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /** @var list<string> */
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
     * Get active cart rows for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $cartRows = Cart::active()
            ->where('user_id', $user->id)
            ->with(self::RELATIONSHIP)
            ->simplePaginate($request->integer('per_page', 10));

        return static::successResponse([
            'carts' => CartResource::collection($cartRows),
            'total' => $this->cartTotal($cartRows),
        ], __('api.cart.retrieved'));
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'vendor_inventory_id' => ['required', 'integer', 'exists:vendor_inventories,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $inventory = VendorInventory::query()
            ->whereKey($validatedData['vendor_inventory_id'])
            ->firstOrFail();

        $quantity = (float) $validatedData['quantity'];
        if ((float) $inventory->stock_quantity < $quantity) {
            return static::errorResponse(__('api.cart.insufficient_stock'));
        }

        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->where('vendor_inventory_id', $inventory->id)
            ->first();

        if ($cartRow instanceof Cart) {
            $newQuantity = (float) $cartRow->quantity + $quantity;
            if ((float) $inventory->stock_quantity < $newQuantity) {
                return static::errorResponse(__('api.cart.insufficient_stock_total'));
            }

            $cartRow->quantity = $newQuantity;
            $cartRow->save();
        } else {
            Cart::create([
                'user_id' => $user->id,
                'vendor_inventory_id' => $inventory->id,
                'quantity' => $quantity,
                'cart_status_id' => CartStatus::ACTIVE_ID,
            ]);
        }

        return $this->index($request);
    }

    /**
     * Update cart row quantity.
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->whereKey($itemId)
            ->firstOrFail();

        $quantity = (float) $validatedData['quantity'];
        if ((float) $cartRow->vendorInventory->stock_quantity < $quantity) {
            return static::errorResponse(__('api.cart.insufficient_stock'));
        }

        $cartRow->quantity = $quantity;
        $cartRow->save();

        return $this->index($request);
    }

    /**
     * Mark an item as removed from the cart.
     */
    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->whereKey($itemId)
            ->firstOrFail();

        $cartRow->cart_status_id = CartStatus::REMOVED_ID;
        $cartRow->save();

        return $this->index($request);
    }

    /**
     * Convert active cart rows into an order.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validatedData = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'delivery_date' => ['required', 'date'],
            'delivery_slot' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = DB::transaction(function () use ($user, $validatedData): Order {
            $cartRows = Cart::active()
                ->where('user_id', $user->id)
                ->with([
                    'vendorInventory.product',
                    'vendorInventory.unit',
                ])
                ->lockForUpdate()
                ->get();

            if ($cartRows->isEmpty()) {
                abort(422, __('api.cart.empty'));
            }

            foreach ($cartRows as $cartRow) {
                if ((float) $cartRow->vendorInventory->stock_quantity < (float) $cartRow->quantity) {
                    abort(422, __('api.cart.insufficient_stock'));
                }
            }

            $subtotal = 0.0;
            $discountAmount = 0.0;

            foreach ($cartRows as $cartRow) {
                $inventory = $cartRow->vendorInventory;
                $originalItemTotal = (float) $cartRow->quantity * (float) $inventory->price;
                $discountedItemTotal = (float) $cartRow->quantity * $inventory->discounted_price;

                $subtotal += $originalItemTotal;
                $discountAmount += ($originalItemTotal - $discountedItemTotal);
            }

            $total = $subtotal - $discountAmount;

            $order = Order::query()->create([
                'user_id' => $user->id,
                'address_id' => $validatedData['address_id'],
                'order_type_id' => OrderType::STANDARD,
                'order_status_id' => OrderStatus::PENDING,
                'payment_status_id' => PaymentStatus::PENDING,
                'delivery_date' => $validatedData['delivery_date'],
                'delivery_slot' => $validatedData['delivery_slot'],
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'delivery_fee' => 0,
                'tax_amount' => 0,
                'total_amount' => round($total, 2),
                'notes' => $validatedData['notes'] ?? null,
            ]);

            foreach ($cartRows as $cartRow) {
                $inventory = $cartRow->vendorInventory;

                $order->items()->create([
                    'vendor_inventory_id' => $inventory->id,
                    'product_name_snapshot' => $inventory->product->name_en,
                    'unit_snapshot' => $inventory->unit->symbol,
                    'unit_price_snapshot' => $inventory->discounted_price,
                    'quantity' => $cartRow->quantity,
                    'subtotal' => (float) $cartRow->quantity * $inventory->discounted_price,
                ]);
            }

            Cart::query()
                ->whereIn('id', $cartRows->pluck('id'))
                ->update(['cart_status_id' => CartStatus::CHECKED_OUT]);

            return $order->load(
                'items.vendorInventory.product',
                'items.vendorInventory.unit',
                'status',
                'paymentStatus',
            );
        });

        return static::successResponse($order, __('api.cart.checked_out'));
    }

    /**
     * @param  iterable<Cart>  $cartRows
     */
    private function cartTotal(iterable $cartRows): float
    {
        $total = 0.0;

        foreach ($cartRows as $cartRow) {
            $total += (float) $cartRow->quantity * $cartRow->vendorInventory->discounted_price;
        }

        return round($total, 2);
    }
}
