<?php

$files = [
    'app/Filament/Vendor/Resources/Wallets/Pages/ListWallets.php',
    'app/Filament/Vendor/Resources/ProductInventories/Tables/ProductInventoryTable.php',
    'app/Filament/Vendor/Resources/ProductInventories/Schemas/ProductInventoryForm.php',
    'app/Filament/Vendor/Resources/Orders/Tables/OrdersTable.php',
    'app/Filament/Vendor/Pages/Schemas/AddToStoreForm.php',
    'app/Filament/Vendor/Pages/ProductCatalog.php',
    'app/Filament/Admin/Resources/WalletTransactions/Tables/WalletTransactionsTable.php',
    'app/Filament/Admin/Resources/Vendors/Tables/VendorsTable.php',
    'app/Filament/Admin/Resources/Vendors/Schemas/VendorForm.php',
    'app/Filament/Admin/Resources/VendorInventories/Tables/VendorInventoryTable.php',
    'app/Filament/Admin/Resources/Users/Tables/UsersTable.php',
    'app/Filament/Admin/Resources/Products/Tables/ProductsTable.php',
    'app/Filament/Admin/Resources/Products/Schemas/ProductForm.php',
    'app/Filament/Admin/Resources/Payouts/Schemas/PayoutForm.php',
    'app/Filament/Admin/Resources/ProductCategories/Schemas/ProductCategoryForm.php',
    'app/Filament/Admin/Resources/Orders/Tables/OrdersTable.php',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) continue;

    $content = file_get_contents($path);

    // This regex looks for ->options( ... ->pluck('translated_name', 'id') )
    // We want to replace it with ->options(fn () => ... ->pluck('translated_name', 'id'))
    // However, it can span multiple lines.
    
    // Instead of a complex regex, we can use a callback.
    $newContent = preg_replace_callback(
        '/->options\(\s*([A-Za-z0-9_\\\\]+::[^)]*?->pluck\(\'translated_name\',\s*\'id\'\))\s*\)/s',
        function ($matches) {
            return '->options(fn () => ' . $matches[1] . ')';
        },
        $content
    );

    if ($newContent !== $content) {
        file_put_contents($path, $newContent);
        echo "Updated: $file\n";
    }
}
