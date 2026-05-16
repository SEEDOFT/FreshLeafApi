<?php

declare(strict_types=1);

use App\Models\Setting;
use Filament\Forms\Components\Select;

if (! function_exists('app_setting')) {
    /**
     * Get or set a setting value.
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('get_dial_code')) {
    /**
     * Get dial code
     */
    function get_dial_code(?string $countryIso, string $default = '+855'): string
    {
        if (blank($countryIso)) {
            return $default;
        }

        $country = country(strtolower($countryIso));

        if (! ($country instanceof \Rinvex\Country\Country)) {
            throw new InvalidArgumentException('Invalid country iso');
        }

        $dialCode = $country->getCallingCode();

        return filled($dialCode) ? '+'.$dialCode : $default;
    }
}

if (! function_exists('get_country_options')) {
    /**
     * Get list of country
     *
     * @return array<string, string>
     */
    function get_country_options(): array
    {
        $options = [];

        foreach (countries() as $country) {
            $iso = $country['iso_3166_1_alpha2'];
            $c = country(strtolower($iso));
            $name = ($c instanceof \Rinvex\Country\Country) ? $c->getName() : 'Unknown';
            $dialCode = get_dial_code($iso);
            $flagUrl = 'https://flagcdn.com/24x18/'.strtolower($iso).'.png';

            $options[$iso] = "
                <span class='freshleaf-country-option'>
                    <img class='freshleaf-country-option-flag'
                         src='{$flagUrl}'
                         width='24' height='18'
                         alt='{$name}'>
                    <span class='freshleaf-country-option-name'>{$name}</span>
                    <span class='freshleaf-country-option-code'>{$dialCode}</span>
                </span>
            ";
        }

        return $options;
    }
}

if (! function_exists('get_country_selected_option_label')) {
    /**
     * Get selected country option
     */
    function get_country_selected_option_label(mixed $value): string
    {
        $iso = strtolower((string) $value);
        $dialCode = get_dial_code($iso);

        return "
            <span class='freshleaf-country-option freshleaf-country-option-selected'>
                <img class='freshleaf-country-option-flag'
                     src='https://flagcdn.com/24x18/{$iso}.png'
                     width='24' height='18'
                     alt='{$value}'>
                <span class='freshleaf-country-option-code'>{$dialCode}</span>
            </span>
        ";
    }
}

if (! function_exists('configure_country_select')) {
    /**
     * Select component for country
     */
    function configure_country_select(Select $select): Select
    {
        return $select
            ->options(get_country_options())
            ->default('KH')
            ->allowHtml()
            ->searchable()
            ->optionsLimit(250)
            ->getOptionLabelUsing(static fn (mixed $value): string => get_country_selected_option_label($value));
    }
}

if (! function_exists('get_country_options_by_dial_code')) {
    /**
     * Get list of country by dial code
     *
     * @return array<string, string>
     */
    function get_country_options_by_dial_code(string $dialCode): array
    {
        $options = [];

        foreach (countries() as $country) {
            $iso = $country['iso_3166_1_alpha2'];
            $countryDialCode = get_dial_code($iso);

            if ($countryDialCode !== $dialCode) {
                continue;
            }

            $c = country(strtolower($iso));
            $name = ($c instanceof \Rinvex\Country\Country) ? $c->getName() : 'Unknown';
            $flagUrl = 'https://flagcdn.com/24x18/'.strtolower($iso).'.png';

            $options[$iso] = "
                <span class='freshleaf-country-option'>
                    <img class='freshleaf-country-option-flag'
                         src='{$flagUrl}'
                         width='24' height='18'
                         alt='{$name}'>
                    <span class='freshleaf-country-option-name'>{$name}</span>
                    <span class='freshleaf-country-option-code'>{$countryDialCode}</span>
                </span>
            ";
        }

        return $options;
    }
}

if (! function_exists('configure_country_select')) {
    /**
     * Configure country select
     */
    function configure_country_select(Select $select): Select
    {
        $options = get_country_options_by_dial_code('+855');

        return $select
            ->options($options)
            ->default('KH')
            ->allowHtml()
            ->searchable(false)  // disable search — only one option
            ->optionsLimit(1)
            ->getOptionLabelUsing(static fn (mixed $value): string => get_country_selected_option_label($value));
    }
}

if (! function_exists('translate')) {
    /**
     * Translate text
     */
    function translate(?string $valueEn, ?string $valueKm): ?string
    {
        return request()->header('Accept-Language') === 'km' ? $valueKm : $valueEn;
    }
}
