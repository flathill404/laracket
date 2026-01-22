<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketUserType: string
{
    case Assignee = 'assignee';
    case Reviewer = 'reviewer';
}
