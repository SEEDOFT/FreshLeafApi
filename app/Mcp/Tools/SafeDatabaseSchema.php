<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Throwable;

#[IsReadOnly]
class SafeDatabaseSchema extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Read the database schema for the application, strictly limited to non-sensitive business data tables. Returns table names and columns.';

    /**
     * Sensitive tables that are explicitly forbidden.
     *
     * @var array<int, string>
     */
    protected array $blockedKeywords = [
        'users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'personal_access_tokens',
        'user_profiles', 'admin_profiles', 'vendor_profiles', 'addresses',
        'wallets', 'wallet_histories', 'wallet_transactions', 'wallet_transaction_histories',
        'ai_chat_sessions', 'ai_chat_messages', 'support_tickets', 'support_messages',
        'user_devices', 'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
        'filament_notifications', 'notifications', 'notification_types', 'notification_statuses',
        'conversations', 'conversation_participants', 'messages', 'conversation_types', 'conversation_statuses',
    ];

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'database' => $schema->string()
                ->description("Name of the database connection to dump (defaults to app's default connection)"),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $connection = $request->get('database') ?? config('database.default');

        try {
            $schemaBuilder = Schema::connection($connection);
            $tables = $schemaBuilder->getTables();
            $result = [];

            foreach ($tables as $table) {
                $tableName = $table['name'];

                if ($this->isTableBlocked($tableName)) {
                    continue;
                }

                $columns = [];
                foreach ($schemaBuilder->getColumns($tableName) as $column) {
                    $columns[$column['name']] = [
                        'type' => $column['type'],
                        'nullable' => $column['nullable'],
                    ];
                }

                $result[$tableName] = [
                    'columns' => $columns,
                ];
            }

            return Response::json([
                'tables' => $result,
            ]);
        } catch (Throwable $e) {
            return Response::error('Failed to retrieve schema: '.$e->getMessage());
        }
    }

    /**
     * Check if a table is blocked.
     */
    protected function isTableBlocked(string $tableName): bool
    {
        $lowerTableName = strtolower($tableName);

        foreach ($this->blockedKeywords as $blocked) {
            if ($lowerTableName === strtolower($blocked) || str_contains($lowerTableName, strtolower($blocked))) {
                return true;
            }
        }

        return false;
    }
}
