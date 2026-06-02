<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Override;

class Dashboard extends Page
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    #[Override]
    protected static ?int $navigationSort = -2;

    #[Override]
    protected string $view = 'filament.pages.shared.dashboard';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.dashboard');
    }

    #[Override]
    public function getHeading(): string
    {
        return __('admin.navigation.dashboard');
    }

    public function getSubheading(): string
    {
        return Carbon::now()->format('l j F, Y');
    }

    #[Override]
    public static function getRoutePath(Panel $panel): string
    {
        return '/';
    }

    #[Override]
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getModule(): string
    {
        return 'admin';
    }

    public function getGreeting(): string
    {
        $hour = Carbon::now()->hour;

        return match (true) {
            $hour < 12 => 'morning',
            $hour < 17 => 'afternoon',
            default => 'evening',
        };
    }

    public function getUserFirstName(): string
    {
        return Auth::user()->first_name ?? 'User';
    }

    public function getUserInitials(): string
    {
        $name = Auth::user()->first_name ?? 'User';
        $words = explode(' ', $name);

        return collect($words)
            ->take(2)
            ->map(static fn (string $word): string => strtoupper($word[0]))
            ->implode('');
    }
}
