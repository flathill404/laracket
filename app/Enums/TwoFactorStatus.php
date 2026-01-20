<?php

namespace App\Enums;

enum TwoFactorStatus: string
{
    case Disabled = 'disabled';
    case Pending = 'pending';
    case Enabled = 'enabled';
}
