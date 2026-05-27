<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use RuntimeException;

use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmul;
use function bcsub;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_repeat;
use function str_starts_with;
use function strpbrk;
use function substr;

final class MoneyService
{
    private const int MONEY_SCALE = 2;

    private const int RATE_SCALE = 8;

    private const int CALCULATION_SCALE = 8;

    public static function money(
        string|int|float|null $value
    ): string {
        return self::roundDecimal(self::decimal($value), self::MONEY_SCALE);
    }

    public static function rate(
        string|int|float|null $value
    ): string {
        return self::roundDecimal(self::decimal($value), self::RATE_SCALE);
    }

    public static function quantity(
        string|int|float|null $value
    ): string {
        return self::roundDecimal(self::decimal($value), self::MONEY_SCALE);
    }

    public static function add(
        string $left,
        string $right,
        int $scale = self::MONEY_SCALE
    ): string {
        return self::roundDecimal(bcadd($left, $right, self::CALCULATION_SCALE), $scale);
    }

    public static function sub(
        string $left,
        string $right,
        int $scale = self::MONEY_SCALE
    ): string {
        return self::roundDecimal(bcsub($left, $right, self::CALCULATION_SCALE), $scale);
    }

    public static function mul(
        string $left,
        string $right,
        int $scale = self::MONEY_SCALE
    ): string {
        return self::roundDecimal(bcmul($left, $right, self::CALCULATION_SCALE), $scale);
    }

    public static function div(
        string $left,
        string $right,
        int $scale = self::MONEY_SCALE
    ): string {
        if (bccomp($right, '0', self::CALCULATION_SCALE) === 0) {
            throw new RuntimeException('Cannot divide money by zero.');
        }

        return self::roundDecimal(bcdiv($left, $right, self::CALCULATION_SCALE), $scale);
    }

    public static function compare(
        string|int|float|null $left,
        string|int|float|null $right,
        int $scale = self::MONEY_SCALE
    ): int {
        return bccomp(self::decimal($left), self::decimal($right), $scale);
    }

    public static function discountUnitPrice(
        string|int|float|null $price,
        string|int|float|null $discountPercentage
    ): string {
        $percentage = self::boundedPercentage($discountPercentage);
        $discountMultiplier = self::sub('100', $percentage, self::CALCULATION_SCALE);
        $discountMultiplier = self::div($discountMultiplier, '100', self::CALCULATION_SCALE);

        return self::mul(self::money($price), $discountMultiplier);
    }

    public static function convert(
        string|int|float|null $amount,
        int $fromCurrencyId,
        int $toCurrencyId
    ): string {
        if ($fromCurrencyId === $toCurrencyId) {
            return self::money($amount);
        }

        $rate = ExchangeRate::getRate($fromCurrencyId, $toCurrencyId);

        return self::mul(self::money($amount), $rate);
    }

    /**
     * @return array{USD: string, KHR: string}
     */
    public static function displayTotals(
        string|int|float|null $amount,
        int $sourceCurrencyId
    ): array {
        return [
            Currency::USD => self::convert($amount, $sourceCurrencyId, Currency::USD_ID),
            Currency::KHR => self::convert($amount, $sourceCurrencyId, Currency::KHR_ID),
        ];
    }

    /**
     * @return array{USD: string, KHR: string}
     */
    public static function displayTotalsFromUsd(
        string|int|float|null $amount
    ): array {
        return self::displayTotals($amount, Currency::USD_ID);
    }

    private static function boundedPercentage(
        string|int|float|null $value
    ): string {
        $percentage = self::decimal($value);

        if (bccomp($percentage, '0', self::CALCULATION_SCALE) < 0) {
            return '0.00';
        }

        if (bccomp($percentage, '100', self::CALCULATION_SCALE) > 0) {
            return '100.00';
        }

        return self::money($percentage);
    }

    private static function decimal(
        string|int|float|null $value
    ): string {
        if ($value === null || $value === '') {
            return '0';
        }

        $decimal = (string) $value;

        if (strpbrk($decimal, 'eE') !== false) {
            $decimal = sprintf('%.'.self::CALCULATION_SCALE.'F', (float) $decimal);
        }

        if (! preg_match('/^-?\d+(\.\d+)?$/', $decimal)) {
            return '0';
        }

        return $decimal;
    }

    private static function roundDecimal(
        string $value,
        int $scale
    ): string {
        $value = self::decimal($value);
        $negative = str_starts_with($value, '-');
        $absolute = $negative ? substr($value, 1) : $value;
        $half = '0.'.str_repeat('0', $scale).'5';
        $rounded = bcadd($absolute, $half, $scale + 1);

        $normalized = bcadd($rounded, '0', $scale);
        $normalized = ltrim($normalized, '+');

        return $negative && bccomp($normalized, '0', $scale) !== 0
            ? '-'.$normalized
            : $normalized;
    }
}
