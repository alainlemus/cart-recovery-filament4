<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class LanguageSwitcher extends Widget
{
    protected static string $view = 'filament.widgets.language-switcher';

    protected static ?int $sort = -100;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }
}
