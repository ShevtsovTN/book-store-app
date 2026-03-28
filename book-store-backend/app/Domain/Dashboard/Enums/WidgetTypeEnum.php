<?php

namespace App\Domain\Dashboard\Enums;

enum WidgetTypeEnum: string
{
    case CARD = 'card';
    case CHART = 'chart';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
