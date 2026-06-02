<?php

declare(strict_types=1);

use App\Mcp\Servers\BusinessDataServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/business-data', BusinessDataServer::class);
