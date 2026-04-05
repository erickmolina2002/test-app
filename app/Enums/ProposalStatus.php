<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Pending = 'pending';
    case Registered = 'registered';
    case Completed = 'completed';
    case Failed = 'failed';
}
