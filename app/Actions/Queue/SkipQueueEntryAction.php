<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\QueueEntry;
use Illuminate\Validation\ValidationException;

class SkipQueueEntryAction
{
    public function execute(QueueEntry $queueEntry): QueueEntry
    {
        if (! in_array($queueEntry->status, [
            QueueStatus::Waiting,
            QueueStatus::Called,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'This queue entry cannot be skipped.',
            ]);
        }

        $queueEntry->update([
            'status' => QueueStatus::Skipped,
        ]);

        return $queueEntry->fresh();
    }
}