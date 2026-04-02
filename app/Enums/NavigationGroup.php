<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup implements HasLabel
{
    case Dashboards;
    case Finances;
    case Settings;

    public function getLabel(): string
    {
        return match ($this) {
            self::Dashboards => 'Dashboards',
            self::Finances => 'Finances',
            self::Settings => 'Settings',
        };
    }
}
