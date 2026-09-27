<?php

namespace App\Enum;

enum ReservationTransfereType: string
{
    case ONE_WAY = 'one_way';
    case RETURN = 'return';
}