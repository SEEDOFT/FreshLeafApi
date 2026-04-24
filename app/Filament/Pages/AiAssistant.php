<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class AiAssistant extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'AI Assistant';

    protected static ?string $slug = 'ai-assistant';

    protected static ?int $navigationSort = 100;

    protected string|Width|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.pages.ai-assistant';
}
