<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetMyOrdersTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Retrieve a list of orders that belong to the currently authenticated consumer.';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('The maximum number of orders to return. Defaults to 10.')
                ->default(10),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $userId = Auth::id();

        if (! $userId) {
            return Response::error('Unauthenticated. You must be logged in to access your orders.');
        }

        $limit = $request->integer('limit', 10);
        $limit = min($limit, 50);

        $orders = Order::with(['items', 'status', 'type', 'vendor'])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();

        return Response::json([
            'orders' => $orders,
        ]);
    }
}
