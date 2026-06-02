<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\VendorInventory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetMyStockItemsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Retrieve the current stock items (vendor inventories) for the authenticated vendor.';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('The maximum number of stock items to return. Defaults to 20.')
                ->default(20),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $vendorId = Auth::id();

        if (! $vendorId) {
            return Response::error('Unauthenticated. You must be logged in as a vendor to access your stock items.');
        }

        $limit = $request->integer('limit', 20);
        $limit = min($limit, 100);

        $items = VendorInventory::with(['product', 'currency', 'unit', 'status', 'activeDiscount'])
            ->where('vendor_id', $vendorId)
            ->latest()
            ->limit($limit)
            ->get();

        return Response::json([
            'stock_items' => $items,
        ]);
    }
}
