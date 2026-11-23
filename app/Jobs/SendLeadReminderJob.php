<?php

namespace App\Jobs;

use App\Models\LeadReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeadReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LeadReminder $reminder) {}

    public function handle(): void
    {
        // TODO: send notification (email / in-app) here
        // Example: $this->reminder->lead->assignedUser->notify(new LeadReminderNotification($this->reminder));

        $this->reminder->update(['is_sent' => true]);
    }
}
