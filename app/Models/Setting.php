<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Override;

use function is_array;
use function is_bool;
use function is_numeric;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $group
 * @property string $type
 * @property Carbon|null $deleted_at
 */
#[Table('settings', key: 'id', keyType: 'int')]
#[Fillable(['key', 'value', 'group', 'type'])]
class Setting extends Model
{
    use SoftDeletes;

    /**
     * {@inheritDoc}
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'key' => 'string',
            'value' => 'string',
            'group' => 'string',
            'type' => 'string',
        ];
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever("setting.{$key}", function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        $type = self::getValueType($value);
        $encodedValue = self::serializeValue($value, $type);

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $encodedValue,
                'group' => $group,
                'type' => $type,
            ]
        );

        Cache::forget("setting.{$key}");
    }

    private static function getValueType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_numeric($value)) {
            return 'number';
        }
        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }

    private static function serializeValue(mixed $value, string $type): string|false|null
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'json') {
            return json_encode($value);
        }

        if ($type === 'boolean') {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value === '1' || $value === 'true',
            'number' => is_numeric($value)
                ? (str_contains($value, '.')
                    ? (float) $value
                    : (int) $value
                )
                : 0,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
