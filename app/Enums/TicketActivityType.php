<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketActivityType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Commented = 'commented';
}
