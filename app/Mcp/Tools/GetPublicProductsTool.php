<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetPublicProductsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Retrieve a list of public products available for consumers.';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()
                ->description('Optional search term to filter products by name.')
                ->default(''),
            'limit' => $schema->integer()
                ->description('The maximum number of products to return. Defaults to 10.')
                ->default(10),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $limit = $request->integer('limit', 10);
        $limit = min($limit, 50);

        $search = $request->string('search')->toString();

        $query = Product::with(['category', 'type', 'status'])
            ->active();

        if ($search) {
            $query->where('name_en', 'like', '%'.$search.'%')
                ->orWhere('name_km', 'like', '%'.$search.'%');
        }

        $products = $query->latest()->limit($limit)->get();

        return Response::json([
            'products' => $products,
        ]);
    }
}
