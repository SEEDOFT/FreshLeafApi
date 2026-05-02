<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\Support\Colors\Color;

final class ThemeColors
{
    /**
     * Get the standard Filament palette colors.
     * These are used for the basic UI components (buttons, links, etc).
     *
     * @return array<string, array<int, string>|string>
     */
    public static function getPalette(): array
    {
        return [
            'primary' => '#10b981', // Vibrant Emerald
            'success' => '#059669',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#3b82f6',
            'gray' => Color::Zinc, // Clean Zinc Grays
        ];
    }

    /**
     * Brand specific colors (Green palette).
     *
     * @return array<string, string>
     */
    public const array BRAND = [
        '50' => '#effaf2',
        '100' => '#d9f2df',
        '300' => '#7ccb8e',
        '500' => '#2e9f58',
        '700' => '#1f6e3b',
    ];

    /**
     * Accent colors (Amber/Gold palette).
     *
     * @return array<string, string>
     */
    public const array ACCENT = [
        '500' => '#f4b400',
        '600' => '#d89b00',
    ];

    /**
     * Neutral colors used for backgrounds and text.
     * '50' is usually for Light Mode backgrounds.
     * '900' is usually for Dark Mode backgrounds.
     *
     * @return array<string, string>
     */
    public const array NEUTRAL = [
        '50' => '#f8faf8',
        '100' => '#eef2ef',
        '300' => '#c5cfc7',
        '500' => '#6b756d',
        '900' => '#162018',
    ];

    /**
     * Get all theme tokens as a combined array.
     *
     * @return array<string, array<int, string>>
     */
    public static function getTokens(): array
    {
        return [
            'brand' => self::BRAND,
            'accent' => self::ACCENT,
            'neutral' => self::NEUTRAL,
        ];
    }
}
