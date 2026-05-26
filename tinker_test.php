<?php

use App\Models\Cart;
use App\Models\CartStatus;
use App\Models\User;
use App\Models\VendorInventory;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    $user = User::first();
    $user->ensureDefaultPaymentMethod();

    // Add item to cart
    $vendorInventory = VendorInventory::first();
    Cart::create([
        'user_id' => $user->id,
        'vendor_inventory_id' => $vendorInventory->id,
        'quantity' => '1.0',
        'cart_status_id' => CartStatus::ACTIVE_ID,
    ]);

    // Simulate HTTP request via Kernel so Form Request validation works natively
    $request = Request::create('/api/v1/cart/checkout', 'POST', [
        'address_id' => 1,
        'payment_method_type_code' => 'wallet',
        'order_type_id' => 1,
    ]);
    $request->headers->set('Accept', 'application/json');
    // Auth bypass for testing
    $app['auth']->guard('sanctum')->setUser($user);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $response = $kernel->handle($request);

    dump($response->getContent());
} catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine()."\n";
}
