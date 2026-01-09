<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
