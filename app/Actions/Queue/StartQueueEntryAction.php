<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\QueueEntry;
use Illuminate\Validation\ValidationException;

class StartQueueEntryAction
{
    public function execute(QueueEntry $queueEntry): QueueEntry
    {
        if ($queueEntry->status !== QueueStatus::Called) {
            throw ValidationException::withMessages([
                'status' => 'Only called queue entries can be started.',
            ]);
        }

        $queueEntry->update([
            'status' => QueueStatus::InService,
            'started_at' => now(),
        ]);

        return $queueEntry->fresh();
    }
}