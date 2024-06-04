<?php

namespace App\Services;

class DateService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function filterDate($result, $date, $fieldName)
    {
        if ($date) {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);

            if ($day) {
                $result->whereDate($fieldName, $date);
            } elseif ($month) {
                $result->whereYear($fieldName, $year)
                    ->whereMonth($fieldName, $month);
            } else {
                $result->whereYear($fieldName, $year);
            }
        }

        return $result;
    }
}
