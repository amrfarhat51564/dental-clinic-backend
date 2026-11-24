<?php

namespace App\Enums;

enum QueueStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case InService = 'in_service';
    case Completed = 'completed';
    case Skipped = 'skipped';
}