<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetMyPayoutSummaryTool;
use App\Mcp\Tools\GetMyStockItemsTool;
use App\Mcp\Tools\GetMyVendorOrdersTool;
use Laravel\Mcp\Server;

class VendorDataServer extends Server
{
    /**
     * The server's name.
     */
    protected string $name = 'Vendor Data Server';

    /**
     * The server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The tools provided by the server.
     *
     * @return array<int, class-string>
     */
    protected function tools(): array
    {
        return [
            GetMyVendorOrdersTool::class,
            GetMyStockItemsTool::class,
            GetMyPayoutSummaryTool::class,
        ];
    }
}
