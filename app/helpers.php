<?php

declare(strict_types=1);

use App\Models\Setting;

if (! function_exists('app_setting')) {
    /**
     * Get or set a setting value.
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}
