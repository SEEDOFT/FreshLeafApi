<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\UserCartItemStatus;
use App\Models\UserCartItemType;
use App\Models\UserCartStatus;
use App\Models\UserCartType;
use App\Models\VendorInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get the active cart for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'user_cart_status_id' => UserCartStatus::ACTIVE],
            ['user_cart_type_id' => UserCartType::DEFAULT]
        );

        $cart->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successTrans(new CartResource($cart), 'cart.retrieved');
    }

    /**
     * Add an item to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validated = $request->validate([
            'vendor_inventory_id' => 'required|exists:vendor_inventories,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $inventory = VendorInventory::findOrFail($validated['vendor_inventory_id']);

        if ($inventory->stock_quantity < $validated['quantity']) {
            return static::errorTrans('cart.insufficient_stock');
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'user_cart_status_id' => UserCartStatus::ACTIVE],
            ['user_cart_type_id' => UserCartType::DEFAULT]
        );

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('vendor_inventory_id', $inventory->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            if ($inventory->stock_quantity < $newQuantity) {
                return static::errorTrans('cart.insufficient_stock_total');
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->subtotal = $newQuantity * $inventory->price;
            $cartItem->save();
        } else {
            $cart->items()->create([
                'vendor_inventory_id' => $inventory->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $inventory->price,
                'subtotal' => $validated['quantity'] * $inventory->price,
                'user_cart_item_status_id' => UserCartItemStatus::ACTIVE,
                'user_cart_item_type_id' => UserCartItemType::STANDARD,
            ]);
        }

        $cart->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successTrans(new CartResource($cart), 'cart.item_added');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('user_cart_status_id', UserCartStatus::ACTIVE);
        })->findOrFail($itemId);

        $inventory = $cartItem->vendorInventory;

        if ($inventory->stock_quantity < $validated['quantity']) {
            return static::errorTrans('cart.insufficient_stock');
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->subtotal = $validated['quantity'] * $cartItem->unit_price;
        $cartItem->save();

        $cart = $cartItem->cart->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successTrans(new CartResource($cart), 'cart.item_updated');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('user_cart_status_id', UserCartStatus::ACTIVE);
        })->findOrFail($itemId);

        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->load(['items.vendorInventory.product', 'items.vendorInventory.vendor', 'items.vendorInventory.unit', 'items.status', 'items.type', 'status', 'type']);

        return static::successTrans(new CartResource($cart), 'cart.item_removed');
    }
}
