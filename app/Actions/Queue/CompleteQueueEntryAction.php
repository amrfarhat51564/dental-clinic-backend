<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\QueueEntry;
use Illuminate\Validation\ValidationException;

class CompleteQueueEntryAction
{
    public function execute(QueueEntry $queueEntry): QueueEntry
    {
        if ($queueEntry->status !== QueueStatus::InService) {
            throw ValidationException::withMessages([
                'status' => 'Only in-service queue entries can be completed.',
            ]);
        }

        $queueEntry->update([
            'status' => QueueStatus::Completed,
            'completed_at' => now(),
        ]);

        return $queueEntry->fresh();
    }
}