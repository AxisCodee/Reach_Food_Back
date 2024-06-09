<?php

namespace App\Enums;

enum Status: string
{
    case ACCEPTED = "accepted";
    case CANCELED = "canceled";
    case PENDING = "pending";
    case DELIVERED = "delivered";


}
