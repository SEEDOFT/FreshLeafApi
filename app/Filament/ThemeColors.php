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
            'primary' => '#3b8230', // Fresh leaf green
            'success' => '#3f7d2c',
            'warning' => '#f6b93b',
            'danger' => '#dc4c3f',
            'info' => '#1e88e5',
            'gray' => Color::Stone, // Warm neutral grays
        ];
    }

    /**
     * Brand specific colors (Green palette).
     *
     * @return array<string, string>
     */
    public const array BRAND = [
        '50' => '#eff6ed',
        '100' => '#e2efe0',
        '300' => '#4a8c3a',
        '500' => '#3b8230',
        '700' => '#24601e',
    ];

    /**
     * Accent colors (Brown/Peach palette).
     *
     * @return array<string, string>
     */
    public const array ACCENT = [
        '500' => '#9e6844',
        '600' => '#924a26',
    ];

    /**
     * Neutral colors used for backgrounds and text.
     * '50' is usually for Light Mode backgrounds.
     * '900' is usually for Dark Mode backgrounds.
     *
     * @return array<string, string>
     */
    public const array NEUTRAL = [
        '50' => '#f7f9f5',
        '100' => '#e5ebe4',
        '300' => '#d0d8ce',
        '500' => '#6b8268',
        '900' => '#1a1f18',
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
