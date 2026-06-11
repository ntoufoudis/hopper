<?php

namespace Ntoufoudis\Hopper\Enums;

enum RunStatus: string
{
    case Pending = 'pending';
    case Staging = 'staging';
    case Ready = 'ready';
    case Importing = 'importing';
    case Completed = 'completed';
    case Failed = 'failed';
    case PartiallyCompleted = 'partially_completed';
}
