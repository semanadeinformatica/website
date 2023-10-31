<?php

namespace App\Jobs;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveQuestCode implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Participant $user,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("Removing 'quest_code' from user: {name}", ['name' => $this->user->usertype->name]);

        $this->user->quest_code = null;
        $this->user->save();
    }
}
