<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\CancelUnpaidOrderJob;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use App\Models\PaymentType;
use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function get_class;

class CartService
{
    /** @var list<string> */
    public const array RELATIONSHIP = [
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
     * Active Carts
     *
     * @return Paginator<int, Cart>
     */
    public function getActiveCarts(User $user, int $perPage = 10)
    {
        return Cart::active()
            ->where('user_id', $user->id)
            ->with(self::RELATIONSHIP)
            ->simplePaginate($perPage);
    }

    /**
     * Add to Cart
     */
    public function addToCart(
        User $user,
        int $vendorInventoryId,
        string $quantity
    ): void {
        $inventory = VendorInventory::find($vendorInventoryId);

        if (! $inventory) {
            abort(404, __('api.general.not_found'));
        }

        $formattedQuantity = MoneyService::quantity($quantity);
        if (
            MoneyService::compare(
                $inventory->stock_quantity,
                $formattedQuantity
            ) < 0
        ) {
            abort(422, __('api.cart.insufficient_stock'));
        }

        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->where('vendor_inventory_id', $inventory->id)
            ->first();

        if ($cartRow instanceof Cart) {
            $newQuantity = MoneyService::add(
                (string) $cartRow->quantity,
                $formattedQuantity
            );
            if (
                MoneyService::compare(
                    $inventory->stock_quantity,
                    $newQuantity
                ) < 0
            ) {
                abort(422, __('api.cart.insufficient_stock_total'));
            }

            $cartRow->update([
                'quantity' => $newQuantity,
            ]);

            $this->recordHistory($cartRow);
        } else {
            $cartRow = Cart::create([
                'user_id' => $user->id,
                'vendor_inventory_id' => $inventory->id,
                'quantity' => $formattedQuantity,
                'cart_status_id' => CartStatus::ACTIVE_ID,
            ]);

            $this->recordHistory($cartRow);
        }
    }

    /**
     * Update Cart
     */
    public function updateCart(
        User $user,
        int $itemId,
        string $quantity
    ): void {
        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->find($itemId);

        if (! $cartRow) {
            abort(404, __('api.general.not_found'));
        }

        $formattedQuantity = MoneyService::quantity($quantity);
        if (
            MoneyService::compare(
                $cartRow->vendorInventory->stock_quantity,
                $formattedQuantity,
            ) < 0
        ) {
            abort(422, __('api.cart.insufficient_stock'));
        }

        $cartRow->update([
            'quantity' => $formattedQuantity,
        ]);

        $this->recordHistory($cartRow);
    }

    /**
     * Remove From Cart
     */
    public function removeFromCart(User $user, int $itemId): void
    {
        $cartRow = Cart::active()
            ->where('user_id', $user->id)
            ->find($itemId);

        if (! $cartRow) {
            abort(404, __('api.general.not_found'));
        }

        $cartRow->update([
            'cart_status_id' => CartStatus::REMOVED_ID,
            'deleted_at' => Carbon::now(),
        ]);

        $this->recordHistory($cartRow);
    }

    /**
     * Checkout
     *
     * @param  array<string, mixed>  $validatedData
     */
    public function checkout(User $user, array $validatedData): Order
    {
        try {
            $order = DB::transaction(
                function () use ($user, $validatedData): Order {
                    $cartRows = Cart::active()
                        ->where('user_id', $user->id)
                        ->with([
                            'vendorInventory.product',
                            'vendorInventory.unit',
                            'vendorInventory.currency',
                            'vendorInventory.activeDiscount',
                        ])
                        ->lockForUpdate()
                        ->get();

                    if ($cartRows->isEmpty()) {
                        abort(422, __('api.cart.empty'));
                    }

                    foreach ($cartRows as $cartRow) {
                        if (
                            MoneyService::compare(
                                $cartRow->vendorInventory->stock_quantity,
                                $cartRow->quantity,
                            ) < 0
                        ) {
                            abort(422, __('api.cart.insufficient_stock'));
                        }
                    }

                    $subtotal = '0.00';
                    $discountAmount = '0.00';
                    $lineItems = [];

                    foreach ($cartRows as $cartRow) {
                        $inventory = $cartRow->vendorInventory;
                        $currencyId = $this->inventoryCurrencyId($inventory);
                        $originalUnitUsd = MoneyService::convert(
                            $inventory->price,
                            $currencyId,
                            Currency::USD_ID
                        );
                        $discountedUnitUsd = MoneyService::discountUnitPrice(
                            $originalUnitUsd,
                            $inventory->discount_percentage
                        );
                        $originalItemTotal = MoneyService::mul(
                            (string) $cartRow->quantity,
                            $originalUnitUsd
                        );
                        $discountedItemTotal = MoneyService::mul(
                            (string) $cartRow->quantity,
                            $discountedUnitUsd
                        );
                        $lineDiscount = MoneyService::sub(
                            $originalItemTotal,
                            $discountedItemTotal
                        );

                        $subtotal = MoneyService::add($subtotal, $originalItemTotal);
                        $discountAmount = MoneyService::add($discountAmount, $lineDiscount);
                        $lineItems[$cartRow->id] = [
                            'unit_price' => $discountedUnitUsd,
                            'subtotal' => $discountedItemTotal,
                        ];
                    }

                    $total = MoneyService::sub($subtotal, $discountAmount);

                    $typeId = $validatedData['payment_method_type_id'];

                    $paymentMethod = $user->paymentMethods()
                        ->where('payment_method_type_id', $typeId)
                        ->first();

                    if (! $paymentMethod) {
                        abort(422, 'Invalid payment method.');
                    }

                    $isCod = $typeId === PaymentMethodType::COD_ID;
                    $initialOrderStatus = $isCod
                        ? OrderStatus::PENDING_ID
                        : OrderStatus::AWAITING_PAYMENT_ID;

                    $order = Order::query()->create([
                        'user_id' => $user->id,
                        'address_id' => $validatedData['address_id'],
                        'order_type_id' => $validatedData['order_type_id'],
                        'order_status_id' => $initialOrderStatus,
                        'payment_status_id' => PaymentStatus::PENDING_ID,
                        'currency_id' => Currency::USD_ID, // Normalized to USD
                        'place_order_date' => Carbon::now(),
                        'delivery_date' => $validatedData['delivery_date'] ?? Carbon::now()->toDateString(),
                        'delivery_slot' => $validatedData['delivery_slot'] ?? 'Standard',
                        'subtotal' => $subtotal,
                        'discount_amount' => $discountAmount,
                        'delivery_fee' => '0.00',
                        'tax_amount' => '0.00',
                        'total_amount' => $total,
                        'notes' => $validatedData['notes'] ?? null,
                    ]);

                    $order->histories()->create([
                        'order_status_id' => $initialOrderStatus,
                        'notes' => 'Order placed successfully.',
                    ]);

                    $payment = $order->payments()->create([
                        'type_id' => PaymentType::ORDER_ID,
                        'status_id' => PaymentStatus::PENDING_ID,
                        'payment_method_id' => $paymentMethod->id,
                        'amount' => $total,
                    ]);

                    $payment->histories()->create([
                        'payment_status_id' => PaymentStatus::PENDING_ID,
                        'notes' => 'Payment pending upon order creation.',
                    ]);

                    $order->update(['payment_id' => $payment->id]);

                    foreach ($cartRows as $cartRow) {
                        $inventory = $cartRow->vendorInventory;
                        $lineItem = $lineItems[$cartRow->id];

                        $orderItem = $order->items()->create([
                            'vendor_inventory_id' => $inventory->id,
                            'product_name_snapshot' => $inventory->product->name_en,
                            'unit_snapshot' => $inventory->unit->symbol,
                            'unit_price_snapshot' => $lineItem['unit_price'],
                            'quantity' => $cartRow->quantity,
                            'subtotal' => $lineItem['subtotal'],
                        ]);

                        $deduction = MoneyService::quantity($cartRow->quantity);
                        $newQuantity = MoneyService::sub($inventory->stock_quantity, $deduction);

                        $inventory->update([
                            'stock_quantity' => $newQuantity,
                        ]);

                        $inventory->histories()->create([
                            'quantity_change' => '-'.$deduction,
                            'new_quantity' => $newQuantity,
                            'reference_type' => get_class($orderItem),
                            'reference_id' => $orderItem->id,
                            'reason' => 'Order Placement',
                        ]);
                    }

                    Cart::whereIn('id', $cartRows->pluck('id'))
                        ->update([
                            'cart_status_id' => CartStatus::CHECKED_OUT_ID,
                            'deleted_at' => Carbon::now(),
                        ]);

                    return $order->load(
                        'items.vendorInventory.product',
                        'items.vendorInventory.unit',
                        'items.vendorInventory.currency',
                        'items.vendorInventory.activeDiscount',
                        'status',
                        'paymentStatus',
                        'type',
                    );
                });

            if ($order->order_status_id === OrderStatus::AWAITING_PAYMENT_ID) {
                CancelUnpaidOrderJob::dispatch($order->id)
                    ->delay(Carbon::now()->addMinutes(5));
            }

            return $order;
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(422, 'Checkout failed: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }

    /**
     * Total Cart Price
     *
     * @param  iterable<Cart>  $cartRows
     * @return array{USD: string, KHR: string}
     */
    public function cartTotal(iterable $cartRows): array
    {
        $totalUsd = '0.00';

        foreach ($cartRows as $cartRow) {
            $inventory = $cartRow->vendorInventory;
            $unitPriceUsd = MoneyService::convert(
                $inventory->discounted_price,
                $this->inventoryCurrencyId($inventory),
                Currency::USD_ID
            );
            $totalUsd = MoneyService::add(
                $totalUsd,
                MoneyService::mul((string) $cartRow->quantity, $unitPriceUsd),
            );
        }

        return MoneyService::displayTotalsFromUsd($totalUsd);
    }

    /**
     * Get currency id of vendor inventory.
     *
     * @return int the currency ID of the vendor inventory
     */
    private function inventoryCurrencyId(VendorInventory $inventory): int
    {
        return $inventory->currency->id;
    }

    /**
     * Record a history entry for the cart.
     */
    private function recordHistory(Cart $cartRow): void
    {
        $attributes = collect($cartRow->getAttributes())
            ->except(['id', 'created_at', 'updated_at', 'deleted_at'])
            ->toArray();

        $cartRow->histories()->create($attributes);
    }
}
