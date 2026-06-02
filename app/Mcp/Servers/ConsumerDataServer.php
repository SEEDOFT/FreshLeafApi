<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetMyOrdersTool;
use App\Mcp\Tools\GetPublicProductsTool;
use Laravel\Mcp\Server;

class ConsumerDataServer extends Server
{
    /**
     * The server's name.
     */
    protected string $name = 'Consumer Data Server';

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
            GetMyOrdersTool::class,
            GetPublicProductsTool::class,
        ];
    }
}
