<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadType;
use App\Models\StatusSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeadCallController extends Controller
{
    private function isTestDriveScheduledResponse(?int $statusSourceId): bool
    {
        if (!$statusSourceId) {
            return false;
        }

        $response = StatusSource::where('id', $statusSourceId)->value('response');
        $normalizedResponse = strtolower(trim((string) $response));
        $normalizedResponse = preg_replace('/[\s_-]+/', ' ', $normalizedResponse ?? '');

        return str_contains($normalizedResponse, 'test drive')
            && str_contains($normalizedResponse, 'scheduled');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{validated: array<string, mixed>, lead_test_drive_at: mixed, error?: string}
     */
    private function prepareCallPayload(array $validated): array
    {
        $testDriveAt = $validated['test_drive_at'] ?? null;
        unset($validated['test_drive_at']);

        $leadResponseId = isset($validated['lead_response']) ? (int) $validated['lead_response'] : null;
        $isTestDriveScheduled = $this->isTestDriveScheduledResponse($leadResponseId);

        if ($isTestDriveScheduled && empty($testDriveAt)) {
            return [
                'validated' => $validated,
                'lead_test_drive_at' => null,
                'error' => 'Test Drive Date & Time is required when Lead Response is Test Drive Scheduled.',
            ];
        }

        if ($isTestDriveScheduled) {
            $validated['test_drive_at'] = $testDriveAt;
        }

        return [
            'validated' => $validated,
            'lead_test_drive_at' => $isTestDriveScheduled ? $testDriveAt : null,
        ];
    }

    public function index(Lead $lead)
    {
        $calls = $lead->calls()->with('creator')->orderBy('call_date', 'desc')->get();
        return response()->json($calls);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'call_date' => 'required|date',
            'number' => 'required|string|max:255',
            'type' => 'required|in:incoming,outgoing',
            'duration' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'lead_response' => 'nullable|exists:status_sources,id',
            'test_drive_at' => 'nullable|date|after_or_equal:now',
            'likely_purchase' => 'nullable|exists:lead_types,likely_purchase',
            'next_follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        $prepared = $this->prepareCallPayload($validated);
        if (isset($prepared['error'])) {
            return response()->json([
                'success' => false,
                'message' => $prepared['error'],
            ], 422);
        }

        $validated = $prepared['validated'];
        $validated['created_by'] = Auth::id();
        $call = $lead->calls()->create($validated);

        $lead->test_drive_at = $prepared['lead_test_drive_at'];
        $lead->save();

        // Sync lead likely_purchase / lead_type from latest follow-up
        $this->syncLeadLikelyPurchase($lead);

        // Log activity
        $lead->activities()->create([
            'activity_type' => 'call_logged',
            'title' => 'Call Logged',
            'description' => ucfirst($validated['type']) . ' call logged by ' . Auth::user()->name,
            'activity_date' => now(),
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Call logged successfully.']);
        }

        return back()->with('success', 'Call logged successfully.');
    }

    public function update(Request $request, LeadCall $call)
    {
        if (Carbon::parse($call->call_date)->isToday()) {
            $message = 'Today follow-ups cannot be edited.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $validated = $request->validate([
            'call_date' => 'required|date',
            'number' => 'required|string|max:255',
            'type' => 'required|in:incoming,outgoing',
            'duration' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'lead_response' => 'nullable|exists:status_sources,id',
            'test_drive_at' => 'nullable|date|after_or_equal:now',
            'likely_purchase' => 'nullable|exists:lead_types,likely_purchase',
            'next_follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        $prepared = $this->prepareCallPayload($validated);
        if (isset($prepared['error'])) {
            return response()->json([
                'success' => false,
                'message' => $prepared['error'],
            ], 422);
        }

        $validated = $prepared['validated'];
        $call->update($validated);

        $lead = $call->lead;
        if ($lead) {
            $lead->test_drive_at = $prepared['lead_test_drive_at'];
            $lead->save();
        }

        // Sync lead likely_purchase / lead_type from latest follow-up
        $this->syncLeadLikelyPurchase($call->lead);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Call updated successfully.']);
        }

        return back()->with('success', 'Call updated successfully.');
    }

    public function destroy(Request $request, LeadCall $call)
    {
        $lead = $call->lead;
        $call->delete();

        // Re-sync after deletion (in case latest follow-up was removed)
        if ($lead) {
            $this->syncLeadLikelyPurchase($lead);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Call deleted successfully.']);
        }

        return back()->with('success', 'Call deleted successfully.');
    }

    /**
     * Ensure the lead's likely_purchase / lead_type_id and lead_response / lead_status_id
     * follow the latest relevant follow-up.
     */
    protected function syncLeadLikelyPurchase(Lead $lead): void
    {
        // === 1) Sync likely_purchase / lead_type_id from the latest entered call that has likely_purchase ===
        $latestLikelyPurchaseCall = $lead->calls()
            ->whereNotNull('likely_purchase')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestLikelyPurchaseCall) {
            $lead->likely_purchase = $latestLikelyPurchaseCall->likely_purchase;

            // Map likely_purchase string to a LeadType (Hot/Warm/Cold)
            $leadType = LeadType::where('likely_purchase', $latestLikelyPurchaseCall->likely_purchase)->first();
            if ($leadType) {
                $lead->lead_type_id = $leadType->id;
            }
        }

        // === 2) Sync next_follow_up from the latest entered call that has next_follow_up_date ===
        $latestNextFollowUpCall = $lead->calls()
            ->whereNotNull('next_follow_up_date')
            ->orderBy('id', 'desc')
            ->first();

        $lead->next_follow_up = $latestNextFollowUpCall?->next_follow_up_date;

        // === 3) Sync lead_response / lead_status_id from the latest entered call that has a response ===
        $latestResponseCall = $lead->calls()
            ->whereNotNull('lead_response')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestResponseCall && !empty($latestResponseCall->lead_response)) {
            $statusSource = StatusSource::with('leadStatus')->find($latestResponseCall->lead_response);
            if ($statusSource) {
                // Store the selected response on the lead
                $lead->lead_response_id = $statusSource->id;
                $normalizedResponse = strtolower(trim((string) $statusSource->response));
                $normalizedResponse = preg_replace('/[\s_-]+/', ' ', $normalizedResponse ?? '');
                $isTestDriveScheduled = str_contains($normalizedResponse, 'test drive')
                    && str_contains($normalizedResponse, 'scheduled');
                $lead->test_drive_at = $isTestDriveScheduled
                    ? ($latestResponseCall->test_drive_at ?? $lead->test_drive_at)
                    : null;

                // Align lead stage (lead_status_id) with the master mapping (e.g., Active / Closed)
                if ($statusSource->lead_status_id) {
                    $lead->lead_status_id = $statusSource->lead_status_id;
                }

                // If the mapped lead status name is "closed", also mark the lead record as closed
                if ($statusSource->leadStatus && strtolower($statusSource->leadStatus->name) === 'closed') {
                    $lead->status = 'closed';
                }
            }
        } else {
            $lead->test_drive_at = null;
        }

        // Persist any changes applied above
        $lead->save();
    }
}
