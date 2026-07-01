<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,applied,contacted,interview_scheduled,interview_completed,offer_received,rejected,withdrawn'],
        ]);

        $status = $validated['status'];

        $application->update([
            'status' => $status,
            ...$this->getStatusTimestamps($status),
        ]);

        return back()->with('status', "Application status updated to {$status}.");
    }

    private function getStatusTimestamps(string $status): array
    {
        $now = now();

        return match ($status) {
            'applied' => ['applied_at' => $now],
            'interview_scheduled' => ['interview_scheduled_at' => $now],
            'interview_completed' => ['interview_completed_at' => $now],
            'offer_received' => ['offer_received_at' => $now],
            'rejected' => ['rejected_at' => $now],
            'withdrawn' => ['withdrawn_at' => $now],
            default => [],
        };
    }
}
