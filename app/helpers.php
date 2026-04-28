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

if (! function_exists('get_country_codes')) {
    /**
     * Get a comprehensive list of countries with their dial codes.
     *
     * @return array<string, array{code: string, name: string}>
     */
    function get_country_codes(): array
    {
        return [
            'AF' => ['code' => '+93', 'name' => 'Afghanistan'],
            'AL' => ['code' => '+355', 'name' => 'Albania'],
            'DZ' => ['code' => '+213', 'name' => 'Algeria'],
            'AD' => ['code' => '+376', 'name' => 'Andorra'],
            'AO' => ['code' => '+244', 'name' => 'Angola'],
            'AG' => ['code' => '+1', 'name' => 'Antigua and Barbuda'],
            'AR' => ['code' => '+54', 'name' => 'Argentina'],
            'AM' => ['code' => '+374', 'name' => 'Armenia'],
            'AU' => ['code' => '+61', 'name' => 'Australia'],
            'AT' => ['code' => '+43', 'name' => 'Austria'],
            'AZ' => ['code' => '+994', 'name' => 'Azerbaijan'],
            'BS' => ['code' => '+1', 'name' => 'Bahamas'],
            'BH' => ['code' => '+973', 'name' => 'Bahrain'],
            'BD' => ['code' => '+880', 'name' => 'Bangladesh'],
            'BB' => ['code' => '+1', 'name' => 'Barbados'],
            'BY' => ['code' => '+375', 'name' => 'Belarus'],
            'BE' => ['code' => '+32', 'name' => 'Belgium'],
            'BZ' => ['code' => '+501', 'name' => 'Belize'],
            'BJ' => ['code' => '+229', 'name' => 'Benin'],
            'BT' => ['code' => '+975', 'name' => 'Bhutan'],
            'BO' => ['code' => '+591', 'name' => 'Bolivia'],
            'BA' => ['code' => '+387', 'name' => 'Bosnia and Herzegovina'],
            'BW' => ['code' => '+267', 'name' => 'Botswana'],
            'BR' => ['code' => '+55', 'name' => 'Brazil'],
            'BN' => ['code' => '+673', 'name' => 'Brunei'],
            'BG' => ['code' => '+359', 'name' => 'Bulgaria'],
            'BF' => ['code' => '+226', 'name' => 'Burkina Faso'],
            'BI' => ['code' => '+257', 'name' => 'Burundi'],
            'KH' => ['code' => '+855', 'name' => 'Cambodia'],
            'CM' => ['code' => '+237', 'name' => 'Cameroon'],
            'CA' => ['code' => '+1', 'name' => 'Canada'],
            'CV' => ['code' => '+238', 'name' => 'Cape Verde'],
            'CF' => ['code' => '+236', 'name' => 'Central African Republic'],
            'TD' => ['code' => '+235', 'name' => 'Chad'],
            'CL' => ['code' => '+56', 'name' => 'Chile'],
            'CN' => ['code' => '+86', 'name' => 'China'],
            'CO' => ['code' => '+57', 'name' => 'Colombia'],
            'KM' => ['code' => '+269', 'name' => 'Comoros'],
            'CG' => ['code' => '+242', 'name' => 'Congo'],
            'CK' => ['code' => '+682', 'name' => 'Cook Islands'],
            'CR' => ['code' => '+506', 'name' => 'Costa Rica'],
            'HR' => ['code' => '+385', 'name' => 'Croatia'],
            'CU' => ['code' => '+53', 'name' => 'Cuba'],
            'CY' => ['code' => '+357', 'name' => 'Cyprus'],
            'CZ' => ['code' => '+420', 'name' => 'Czech Republic'],
            'DK' => ['code' => '+45', 'name' => 'Denmark'],
            'DJ' => ['code' => '+253', 'name' => 'Djibouti'],
            'DM' => ['code' => '+1', 'name' => 'Dominica'],
            'DO' => ['code' => '+1', 'name' => 'Dominican Republic'],
            'TL' => ['code' => '+670', 'name' => 'East Timor'],
            'EC' => ['code' => '+593', 'name' => 'Ecuador'],
            'EG' => ['code' => '+20', 'name' => 'Egypt'],
            'SV' => ['code' => '+503', 'name' => 'El Salvador'],
            'GQ' => ['code' => '+240', 'name' => 'Equatorial Guinea'],
            'ER' => ['code' => '+291', 'name' => 'Eritrea'],
            'EE' => ['code' => '+372', 'name' => 'Estonia'],
            'ET' => ['code' => '+251', 'name' => 'Ethiopia'],
            'FJ' => ['code' => '+679', 'name' => 'Fiji'],
            'FI' => ['code' => '+358', 'name' => 'Finland'],
            'FR' => ['code' => '+33', 'name' => 'France'],
            'GA' => ['code' => '+241', 'name' => 'Gabon'],
            'GM' => ['code' => '+220', 'name' => 'Gambia'],
            'GE' => ['code' => '+995', 'name' => 'Georgia'],
            'DE' => ['code' => '+49', 'name' => 'Germany'],
            'GH' => ['code' => '+233', 'name' => 'Ghana'],
            'GR' => ['code' => '+30', 'name' => 'Greece'],
            'GD' => ['code' => '+1', 'name' => 'Grenada'],
            'GT' => ['code' => '+502', 'name' => 'Guatemala'],
            'GN' => ['code' => '+224', 'name' => 'Guinea'],
            'GW' => ['code' => '+245', 'name' => 'Guinea-Bissau'],
            'GY' => ['code' => '+592', 'name' => 'Guyana'],
            'HT' => ['code' => '+509', 'name' => 'Haiti'],
            'HN' => ['code' => '+504', 'name' => 'Honduras'],
            'HK' => ['code' => '+852', 'name' => 'Hong Kong'],
            'HU' => ['code' => '+36', 'name' => 'Hungary'],
            'IS' => ['code' => '+354', 'name' => 'Iceland'],
            'IN' => ['code' => '+91', 'name' => 'India'],
            'ID' => ['code' => '+62', 'name' => 'Indonesia'],
            'IR' => ['code' => '+98', 'name' => 'Iran'],
            'IQ' => ['code' => '+964', 'name' => 'Iraq'],
            'IE' => ['code' => '+353', 'name' => 'Ireland'],
            'IL' => ['code' => '+972', 'name' => 'Israel'],
            'IT' => ['code' => '+39', 'name' => 'Italy'],
            'JM' => ['code' => '+1', 'name' => 'Jamaica'],
            'JP' => ['code' => '+81', 'name' => 'Japan'],
            'JO' => ['code' => '+962', 'name' => 'Jordan'],
            'KZ' => ['code' => '+7', 'name' => 'Kazakhstan'],
            'KE' => ['code' => '+254', 'name' => 'Kenya'],
            'KI' => ['code' => '+686', 'name' => 'Kiribati'],
            'KP' => ['code' => '+850', 'name' => 'North Korea'],
            'KR' => ['code' => '+82', 'name' => 'South Korea'],
            'KW' => ['code' => '+965', 'name' => 'Kuwait'],
            'KG' => ['code' => '+996', 'name' => 'Kyrgyzstan'],
            'LA' => ['code' => '+856', 'name' => 'Laos'],
            'LV' => ['code' => '+371', 'name' => 'Latvia'],
            'LB' => ['code' => '+961', 'name' => 'Lebanon'],
            'LS' => ['code' => '+266', 'name' => 'Lesotho'],
            'LR' => ['code' => '+231', 'name' => 'Liberia'],
            'LY' => ['code' => '+218', 'name' => 'Libya'],
            'LI' => ['code' => '+423', 'name' => 'Liechtenstein'],
            'LT' => ['code' => '+370', 'name' => 'Lithuania'],
            'LU' => ['code' => '+352', 'name' => 'Luxembourg'],
            'MO' => ['code' => '+853', 'name' => 'Macau'],
            'MK' => ['code' => '+389', 'name' => 'Macedonia'],
            'MG' => ['code' => '+261', 'name' => 'Madagascar'],
            'MW' => ['code' => '+265', 'name' => 'Malawi'],
            'MY' => ['code' => '+60', 'name' => 'Malaysia'],
            'MV' => ['code' => '+960', 'name' => 'Maldives'],
            'ML' => ['code' => '+223', 'name' => 'Mali'],
            'MT' => ['code' => '+356', 'name' => 'Malta'],
            'MH' => ['code' => '+692', 'name' => 'Marshall Islands'],
            'MR' => ['code' => '+222', 'name' => 'Mauritania'],
            'MU' => ['code' => '+230', 'name' => 'Mauritius'],
            'MX' => ['code' => '+52', 'name' => 'Mexico'],
            'FM' => ['code' => '+691', 'name' => 'Micronesia'],
            'MD' => ['code' => '+373', 'name' => 'Moldova'],
            'MC' => ['code' => '+377', 'name' => 'Monaco'],
            'MN' => ['code' => '+976', 'name' => 'Mongolia'],
            'ME' => ['code' => '+382', 'name' => 'Montenegro'],
            'MA' => ['code' => '+212', 'name' => 'Morocco'],
            'MZ' => ['code' => '+258', 'name' => 'Mozambique'],
            'MM' => ['code' => '+95', 'name' => 'Myanmar'],
            'NA' => ['code' => '+264', 'name' => 'Namibia'],
            'NR' => ['code' => '+674', 'name' => 'Nauru'],
            'NP' => ['code' => '+977', 'name' => 'Nepal'],
            'NL' => ['code' => '+31', 'name' => 'Netherlands'],
            'NZ' => ['code' => '+64', 'name' => 'New Zealand'],
            'NI' => ['code' => '+505', 'name' => 'Nicaragua'],
            'NE' => ['code' => '+227', 'name' => 'Niger'],
            'NG' => ['code' => '+234', 'name' => 'Nigeria'],
            'NO' => ['code' => '+47', 'name' => 'Norway'],
            'OM' => ['code' => '+968', 'name' => 'Oman'],
            'PK' => ['code' => '+92', 'name' => 'Pakistan'],
            'PW' => ['code' => '+680', 'name' => 'Palau'],
            'PA' => ['code' => '+507', 'name' => 'Panama'],
            'PG' => ['code' => '+675', 'name' => 'Papua New Guinea'],
            'PY' => ['code' => '+595', 'name' => 'Paraguay'],
            'PE' => ['code' => '+51', 'name' => 'Peru'],
            'PH' => ['code' => '+63', 'name' => 'Philippines'],
            'PL' => ['code' => '+48', 'name' => 'Poland'],
            'PT' => ['code' => '+351', 'name' => 'Portugal'],
            'QA' => ['code' => '+974', 'name' => 'Qatar'],
            'RO' => ['code' => '+40', 'name' => 'Romania'],
            'RU' => ['code' => '+7', 'name' => 'Russia'],
            'RW' => ['code' => '+250', 'name' => 'Rwanda'],
            'KN' => ['code' => '+1', 'name' => 'Saint Kitts and Nevis'],
            'LC' => ['code' => '+1', 'name' => 'Saint Lucia'],
            'VC' => ['code' => '+1', 'name' => 'Saint Vincent and the Grenadines'],
            'WS' => ['code' => '+685', 'name' => 'Samoa'],
            'SM' => ['code' => '+378', 'name' => 'San Marino'],
            'ST' => ['code' => '+239', 'name' => 'Sao Tome and Principe'],
            'SA' => ['code' => '+966', 'name' => 'Saudi Arabia'],
            'SN' => ['code' => '+221', 'name' => 'Senegal'],
            'RS' => ['code' => '+381', 'name' => 'Serbia'],
            'SC' => ['code' => '+248', 'name' => 'Seychelles'],
            'SL' => ['code' => '+232', 'name' => 'Sierra Leone'],
            'SG' => ['code' => '+65', 'name' => 'Singapore'],
            'SK' => ['code' => '+421', 'name' => 'Slovakia'],
            'SI' => ['code' => '+386', 'name' => 'Slovenia'],
            'SB' => ['code' => '+677', 'name' => 'Solomon Islands'],
            'SO' => ['code' => '+252', 'name' => 'Somalia'],
            'ZA' => ['code' => '+27', 'name' => 'South Africa'],
            'ES' => ['code' => '+34', 'name' => 'Spain'],
            'LK' => ['code' => '+94', 'name' => 'Sri Lanka'],
            'SD' => ['code' => '+249', 'name' => 'Sudan'],
            'SR' => ['code' => '+597', 'name' => 'Suriname'],
            'SZ' => ['code' => '+268', 'name' => 'Swaziland'],
            'SE' => ['code' => '+46', 'name' => 'Sweden'],
            'CH' => ['code' => '+41', 'name' => 'Switzerland'],
            'SY' => ['code' => '+963', 'name' => 'Syria'],
            'TW' => ['code' => '+886', 'name' => 'Taiwan'],
            'TJ' => ['code' => '+992', 'name' => 'Tajikistan'],
            'TZ' => ['code' => '+255', 'name' => 'Tanzania'],
            'TH' => ['code' => '+66', 'name' => 'Thailand'],
            'TG' => ['code' => '+228', 'name' => 'Togo'],
            'TO' => ['code' => '+676', 'name' => 'Tonga'],
            'TT' => ['code' => '+1', 'name' => 'Trinidad and Tobago'],
            'TN' => ['code' => '+216', 'name' => 'Tunisia'],
            'TR' => ['code' => '+90', 'name' => 'Turkey'],
            'TM' => ['code' => '+993', 'name' => 'Turkmenistan'],
            'TV' => ['code' => '+688', 'name' => 'Tuvalu'],
            'UG' => ['code' => '+256', 'name' => 'Uganda'],
            'UA' => ['code' => '+380', 'name' => 'Ukraine'],
            'AE' => ['code' => '+971', 'name' => 'United Arab Emirates'],
            'GB' => ['code' => '+44', 'name' => 'United Kingdom'],
            'US' => ['code' => '+1', 'name' => 'United States'],
            'UY' => ['code' => '+598', 'name' => 'Uruguay'],
            'UZ' => ['code' => '+998', 'name' => 'Uzbekistan'],
            'VU' => ['code' => '+678', 'name' => 'Vanuatu'],
            'VA' => ['code' => '+379', 'name' => 'Vatican City'],
            'VE' => ['code' => '+58', 'name' => 'Venezuela'],
            'VN' => ['code' => '+84', 'name' => 'Vietnam'],
            'YE' => ['code' => '+967', 'name' => 'Yemen'],
            'ZM' => ['code' => '+260', 'name' => 'Zambia'],
            'ZW' => ['code' => '+263', 'name' => 'Zimbabwe'],
        ];
    }

    if (! function_exists('get_country_options')) {
        /**
         * Get a simple key-value list for select options.
         *
         * @return array<string, string>
         */
        function get_country_options(): array
        {
            return array_map(
                static fn ($item) => "{$item['code']} ({$item['name']})",
                get_country_codes()
            );
        }
    }

    if (! function_exists('get_dial_code')) {
        /**
         * Get the numeric dial code for a country ISO key.
         */
        function get_dial_code(string $iso): string
        {
            return get_country_codes()[$iso]['code'] ?? '+855';
        }
    }
}
