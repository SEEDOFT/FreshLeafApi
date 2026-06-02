<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\SafeDatabaseQuery;
use App\Mcp\Tools\SafeDatabaseSchema;
use Laravel\Mcp\Server;

class BusinessDataServer extends Server
{
    /**
     * The server's name.
     */
    protected string $name = 'Business Data Server';

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
            SafeDatabaseQuery::class,
            SafeDatabaseSchema::class,
        ];
    }
}
