<?php

namespace App\Enums;

enum NotificationActions: string
{
    case ADD = 'add';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case START_TRIP = 'start_trip';
    case CHANGE_PRICE = 'change_price';
    case CHANGE_DATE = 'change_date';
    case TRACE = 'trace';
}
