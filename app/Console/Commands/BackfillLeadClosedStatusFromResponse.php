<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class BackfillLeadClosedStatusFromResponse extends Command
{
    protected $signature = 'leads:backfill-closed-from-response
                            {--dry-run : List affected lead IDs without updating the database}';

    protected $description = 'Set leads.status to closed and align lead_status_id where lead_response_id maps to a closed master (status_sources → lead_statuses)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run: no rows will be updated.');
        }

        $wouldUpdate = 0;
        $sampleIds = [];

        Lead::query()
            ->whereNotNull('lead_response_id')
            ->with([
                'leadResponse' => function ($q) {
                    $q->select('id', 'response', 'lead_status_id')->with([
                        'leadStatus' => function ($q2) {
                            $q2->select('id', 'name');
                        },
                    ]);
                },
            ])
            ->orderBy('id')
            ->chunkById(500, function ($leads) use ($dryRun, &$wouldUpdate, &$sampleIds) {
                foreach ($leads as $lead) {
                    $source = $lead->leadResponse;
                    if (!$source?->leadStatus || strtolower((string) $source->leadStatus->name) !== 'closed') {
                        continue;
                    }

                    $updates = [];
                    if ($lead->status !== 'closed') {
                        $updates['status'] = 'closed';
                    }
                    if ($source->lead_status_id && (int) $lead->lead_status_id !== (int) $source->lead_status_id) {
                        $updates['lead_status_id'] = $source->lead_status_id;
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $wouldUpdate++;
                    if (count($sampleIds) < 20) {
                        $sampleIds[] = $lead->id;
                    }

                    if (!$dryRun) {
                        Lead::query()->whereKey($lead->id)->update($updates);
                    }
                }
            });

        if ($wouldUpdate === 0) {
            $this->info('No leads needed alignment.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? 'Would update' : 'Updated') . " {$wouldUpdate} lead(s).");
        if ($sampleIds !== []) {
            $this->line('Sample lead ID(s): ' . implode(', ', $sampleIds) . ($wouldUpdate > count($sampleIds) ? ', …' : ''));
        }

        return self::SUCCESS;
    }
}
