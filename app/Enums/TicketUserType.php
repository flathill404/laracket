<?php

namespace App\Enums;

enum TicketUserType: string
{
    case Assignee = 'assignee';
    case Reviewer = 'reviewer';
}
