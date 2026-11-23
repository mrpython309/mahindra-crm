<?php

namespace App\Console\Commands;

use App\Jobs\SendLeadReminderJob;
use App\Models\LeadReminder;
use Illuminate\Console\Command;

class DispatchLeadReminders extends Command
{
    protected $signature = 'reminders:dispatch';

    protected $description = 'Dispatch queued jobs for all due lead reminders that have not been sent yet';

    public function handle(): void
    {
        $count = 0;

        LeadReminder::where('reminder_date', '<=', now())
            ->where('is_sent', false)
            ->each(function (LeadReminder $reminder) use (&$count) {
                SendLeadReminderJob::dispatch($reminder);
                $count++;
            });

        $this->info("Dispatched {$count} lead reminder job(s).");
    }
}
