<?php

namespace App\Enums;

enum ActivityAction: string
{
    case Registered = 'registered';
    case SignedIn = 'signed_in';
    case SignedOut = 'signed_out';
}
