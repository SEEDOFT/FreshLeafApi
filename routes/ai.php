<?php

declare(strict_types=1);

use App\Mcp\Servers\BusinessDataServer;
use App\Mcp\Servers\ConsumerDataServer;
use App\Mcp\Servers\VendorDataServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/business-data', BusinessDataServer::class);
Mcp::web('/mcp/consumer-data', ConsumerDataServer::class)->middleware('auth:sanctum');
Mcp::web('/mcp/vendor-data', VendorDataServer::class)->middleware('auth:sanctum');
