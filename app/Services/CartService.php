<?php

declare(strict_types=1);

namespace App\Services;

use App\Filament\Vendor\Resources\Orders\OrderResource;
use App\Jobs\CancelUnpaidOrderJob;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Models\CommissionFee;
use App\Models\CommissionFeeHistory;
use App\Models\Currency;
use App\Models\ExchangeRateHistory;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use App\Models\PaymentType;
use App\Models\User;
use App\Models\VendorInventory;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function get_class;

class CartService
{
    /**
     * Relationships loaded for active carts and general listings.
     *
     * @var list<string>
     */
    private const DEFAULT_RELATIONS = [
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
     * Relationships required specifically for the checkout process to calculate totals correctly.
     *
     * @var list<string>
     */
    private const CHECKOUT_RELATIONS = [
        'vendorInventory.product',
        'vendorInventory.unit',
        'vendorInventory.currency',
        'vendorInventory.activeDiscount',
    ];

    /**
     * Relationships returned with a newly created order after checkout.
     *
     * @var list<string>
     */
    private const CHECKOUT_RETURN_RELATIONS = [
        'items.vendorInventory.product',
        'items.vendorInventory.unit',
        'items.vendorInventory.currency',
        'items.vendorInventory.activeDiscount',
        'status',
        'paymentStatus',
        'type',
    ];

    /**
     * Get paginated active carts for a user.
     *
     * @return Paginator<int, Cart>
     */
    public function getActiveCarts(User $user, int $perPage = 10)
    {
        return Cart::active()
            ->where('user_id', $user->id)
            ->with(self::DEFAULT_RELATIONS)
            ->simplePaginate($perPage);
    }

    /**
     * Add an item to the user's active cart.
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
     * Update the quantity of an item in the cart.
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
     * Remove an item from the active cart.
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
     * Process checkout for the user's active cart.
     *
     * @param  array<string, mixed>  $validatedData
     * @return Collection<int, Order>
     *
     * @throws HttpException
     * @throws RuntimeException
     */
    public function checkout(User $user, array $validatedData): Collection
    {
        try {
            $createdOrders = DB::transaction(
                function () use ($user, $validatedData) {
                    $cartRows = Cart::active()
                        ->where('user_id', $user->id)
                        ->with(self::CHECKOUT_RELATIONS)
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

                    $typeId = $validatedData['payment_method_type_id'];

                    $paymentMethod = $user->paymentMethods()
                        ->where('payment_method_type_id', $typeId)
                        ->first();

                    if (! $paymentMethod) {
                        abort(422, __('api.cart.invalid_payment_method'));
                    }

                    $isCod = $typeId === PaymentMethodType::COD_ID;
                    $isWallet = $typeId === PaymentMethodType::WALLET_ID;
                    $paymentCurrencyId = $validatedData['payment_currency_id'] ?? Currency::USD_ID;

                    $initialOrderStatus = $isCod
                        ? OrderStatus::PENDING_ID
                        : OrderStatus::AWAITING_PAYMENT_ID;

                    $initialPaymentStatus = PaymentStatus::PENDING_ID;

                    $commissionFeeHistory = CommissionFeeHistory::where('commission_fee_id', CommissionFee::ID)->latest()->first();

                    // If paying in a different currency, capture the exact rate used for that conversion
                    $exchangeRateHistory = ExchangeRateHistory::query()
                        ->where('from_currency_id', $paymentCurrencyId === Currency::KHR_ID ? Currency::USD_ID : Currency::KHR_ID)
                        ->where('to_currency_id', $paymentCurrencyId === Currency::KHR_ID ? Currency::KHR_ID : Currency::USD_ID)
                        ->latest()
                        ->first();

                    $vendorGroups = $cartRows->groupBy(fn ($cartRow) => $cartRow->vendorInventory->vendor_id);
                    $orders = new Collection;

                    // Calculate grand total to check wallet balance
                    $grandTotalUsd = '0.00';
                    $vendorTotals = [];

                    foreach ($vendorGroups as $vendorId => $vendorCartRows) {
                        $subtotal = '0.00';
                        $discountAmount = '0.00';
                        $lineItems = [];

                        foreach ($vendorCartRows as $cartRow) {
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

                        $vendorTotal = MoneyService::sub($subtotal, $discountAmount);
                        $grandTotalUsd = MoneyService::add($grandTotalUsd, $vendorTotal);
                        $vendorTotals[$vendorId] = [
                            'subtotal' => $subtotal,
                            'discountAmount' => $discountAmount,
                            'total' => $vendorTotal,
                            'lineItems' => $lineItems,
                        ];
                    }

                    $grandPaymentAmount = $grandTotalUsd;
                    if ($paymentCurrencyId !== Currency::USD_ID) {
                        $grandPaymentAmount = MoneyService::convert($grandTotalUsd, Currency::USD_ID, $paymentCurrencyId);
                    }

                    foreach ($vendorGroups as $vendorId => $vendorCartRows) {
                        $totals = $vendorTotals[$vendorId];

                        $order = Order::query()->create([
                            'user_id' => $user->id,
                            'vendor_id' => $vendorId,
                            'address_id' => $validatedData['address_id'],
                            'order_type_id' => $validatedData['order_type_id'],
                            'order_status_id' => $initialOrderStatus,
                            'payment_status_id' => $initialPaymentStatus,
                            'commission_fee_history_id' => $commissionFeeHistory?->id,
                            'exchange_rate_history_id' => $exchangeRateHistory?->id,
                            'currency_id' => Currency::USD_ID,
                            'place_order_date' => Carbon::now(),
                            'delivery_date' => $validatedData['delivery_date'] ?? Carbon::now()->toDateString(),
                            'delivery_slot' => $validatedData['delivery_slot'] ?? 'Standard',
                            'subtotal' => $totals['subtotal'],
                            'discount_amount' => $totals['discountAmount'],
                            'delivery_fee' => '0.00',
                            'tax_amount' => '0.00',
                            'total_amount' => $totals['total'],
                            'notes' => $validatedData['notes'] ?? null,
                        ]);

                        $order->histories()->create([
                            'order_status_id' => $initialOrderStatus,
                            'notes' => 'Order placed successfully.',
                        ]);

                        $paymentAmount = $totals['total'];
                        if ($paymentCurrencyId !== Currency::USD_ID) {
                            $paymentAmount = MoneyService::convert($paymentAmount, Currency::USD_ID, $paymentCurrencyId);
                        }

                        $payment = $order->payments()->create([
                            'type_id' => PaymentType::ORDER_ID,
                            'status_id' => $initialPaymentStatus,
                            'currency_id' => $paymentCurrencyId,
                            'exchange_rate_history_id' => $exchangeRateHistory?->id,
                            'payment_method_id' => $paymentMethod->id,
                            'amount' => $paymentAmount,
                        ]);

                        $payment->histories()->create([
                            'payment_status_id' => PaymentStatus::PENDING_ID,
                            'notes' => 'Payment pending upon order creation.',
                        ]);

                        $order->update(['payment_id' => $payment->id]);

                        foreach ($vendorCartRows as $cartRow) {
                            $inventory = $cartRow->vendorInventory;
                            $lineItem = $totals['lineItems'][$cartRow->id];

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

                        $orders->push($order);

                    } // end vendor loop

                    Cart::whereIn('id', $cartRows->pluck('id'))
                        ->update([
                            'cart_status_id' => CartStatus::CHECKED_OUT_ID,
                            'deleted_at' => Carbon::now(),
                        ]);

                    return $orders;
                });

            // Post transaction processing
            $createdOrders->each(function ($order) {
                $order->load(self::CHECKOUT_RETURN_RELATIONS);

                if ($order->order_status_id === OrderStatus::AWAITING_PAYMENT_ID) {
                    CancelUnpaidOrderJob::dispatch($order->id)
                        ->delay(Carbon::now()->addMinutes(5));
                }

                $vendor = $order->items->first()->vendorInventory->vendor;
                $url = OrderResource::getUrl('view', ['record' => $order], panel: 'vendor');

                Notification::make()
                    ->title(__('api.notifications.new_order_title'))
                    ->body(__('api.notifications.new_order_body', ['order_number' => $order->order_number]))
                    ->icon('heroicon-o-shopping-bag')
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->label('View Order')
                            ->url($url)
                            ->button(),
                    ])
                    ->sendToDatabase($vendor)
                    ->broadcast($vendor);
            });

            return $createdOrders;
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(422, 'Checkout failed: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
        }
    }

    /**
     * @param  iterable<Cart>  $cartRows
     * @return array<string, string>
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
     * @return int The currency ID of the vendor inventory
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
