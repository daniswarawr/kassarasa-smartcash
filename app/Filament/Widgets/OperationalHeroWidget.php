<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OperationalHeroWidget extends Widget
{
    protected static string $view = 'filament.widgets.operational-hero-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public function isOwner(): bool
    {
        return Auth::user()?->isOwner() ?? true;
    }

    public function getUserName(): string
    {
        return Auth::user()?->name ?? 'Pengguna';
    }
}
