<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class AiAssistant extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function getNavigationLabel(): string
    {
        return __('admin.ai.assistant');
    }

    public function getHeading(): string
    {
        return __('admin.ai.assistant');
    }

    protected static ?string $slug = 'ai-assistant';

    protected static ?int $navigationSort = 100;

    protected string|Width|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.vendor.pages.ai-assistant';
}
