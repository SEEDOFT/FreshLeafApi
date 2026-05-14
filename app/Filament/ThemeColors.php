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
            'primary' => '#2e5321', // Deep leaf green
            'success' => '#3f7d2c',
            'warning' => '#f6b93b',
            'danger' => '#dc4c3f',
            'info' => '#1e88e5',
            'gray' => Color::Zinc, // Clean Zinc Grays
        ];
    }

    /**
     * Brand specific colors (Green palette).
     *
     * @return array<string, string>
     */
    public const array BRAND = [
        '50' => '#fbf8f2',
        '100' => '#f3efe9',
        '300' => '#3f6d2e',
        '500' => '#2e5321',
        '700' => '#1a3314',
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
        '50' => '#fcf9f5',
        '100' => '#e5e5e5',
        '300' => '#bdbdbd',
        '500' => '#6b7260',
        '900' => '#1a1a1a',
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
