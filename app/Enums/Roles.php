<?php

namespace App\Enums;


enum Roles: string
{
    case SUPER_ADMIN = 'super admin';
    case ADMIN = 'admin';
    case SALES_MANAGER = 'sales manager';
    case SALESMAN = 'salesman';
    case CUSTOMER = 'customer';

}
