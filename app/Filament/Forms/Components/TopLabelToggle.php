<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Toggle;

class TopLabelToggle extends Toggle
{
    protected function setUp(): void
    {
        parent::setUp();

        // By default, Filament Toggle places the label inline (beside the switch).
        // Setting inline(false) places the label above the switch.
        $this->inline(false);
    }
}
