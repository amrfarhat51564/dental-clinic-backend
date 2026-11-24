<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\QueueEntry;
use Illuminate\Validation\ValidationException;

class CallQueueEntryAction
{
    public function execute(QueueEntry $queueEntry): QueueEntry
    {
        if ($queueEntry->status !== QueueStatus::Waiting) {
            throw ValidationException::withMessages([
                'status' => 'Only waiting queue entries can be called.',
            ]);
        }

        $queueEntry->update([
            'status' => QueueStatus::Called,
            'called_at' => now(),
        ]);

        return $queueEntry->fresh();
    }
}