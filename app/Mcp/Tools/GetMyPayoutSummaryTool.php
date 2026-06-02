<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Payout;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class GetMyPayoutSummaryTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Retrieve the payout history explicitly belonging to the authenticated vendor.';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('The maximum number of payouts to return. Defaults to 10.')
                ->default(10),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $vendorId = Auth::id();

        if (! $vendorId) {
            return Response::error('Unauthenticated. You must be logged in as a vendor to access your payouts.');
        }

        $limit = $request->integer('limit', 10);
        $limit = min($limit, 50);

        $payouts = Payout::with(['status', 'method'])
            ->where('vendor_user_id', $vendorId)
            ->latest()
            ->limit($limit)
            ->get();

        return Response::json([
            'payouts' => $payouts,
        ]);
    }
}
