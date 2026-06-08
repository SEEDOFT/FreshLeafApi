<?php

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$admin = User::where('user_type_id', 1)->first();
echo 'Admin Wallet Balance: '.Wallet::where('user_id', $admin->id)->where('currency_id', 2)->value('balance')."\n";

$orders = Order::where('order_status_id', 5)->where('is_vendor_paid', true)->get();
$totalMissingCommission = 0;
foreach ($orders as $order) {
    $com = $order->items()->sum('commission_amount');
    echo 'Order '.$order->id.' Commission: '.$com."\n";
    $totalMissingCommission += $com;
}
echo 'Total Missing Commission: '.$totalMissingCommission."\n";
