<?php

namespace App\Actions;

class GetDaysNamesAction
{
    private static array $days = [
        1 => 'Saturday',
        2 => 'Sunday',
        3 => 'Monday',
        4 => 'Tuesday',
        5 => 'Wednesday',
        6 => 'Thursday',
        7 => 'Friday',
    ];

    public static function handle(array $daysNumbers): array
    {
        return array_intersect_key(self::$days, array_flip($daysNumbers));
    }
}
