<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function store(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'interview_type' => ['required', 'string', 'in:hr,technical,founder,management,culture_fit'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'location' => ['sometimes', 'nullable', 'string', 'max:500'],
            'meeting_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'interviewer_details' => ['sometimes', 'nullable', 'array'],
        ]);

        $interview = Interview::create([
            ...$validated,
            'application_id' => $application->id,
            'user_id' => $request->user()->id,
            'status' => Interview::STATUS_SCHEDULED,
        ]);

        if ($application->status === Application::STATUS_APPLIED || $application->status === Application::STATUS_DRAFT) {
            $application->update([
                'status' => Application::STATUS_INTERVIEW_SCHEDULED,
                'interview_scheduled_at' => $validated['scheduled_at'],
            ]);
        }

        return back()->with('status', "Interview scheduled for {$interview->scheduled_at->format('M j, Y g:i A')}.");
    }

    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $validated = $request->validate([
            'interview_type' => ['sometimes', 'string', 'in:hr,technical,founder,management,culture_fit'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'date', 'after:now'],
            'location' => ['sometimes', 'nullable', 'string', 'max:500'],
            'meeting_link' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'interviewer_details' => ['sometimes', 'nullable', 'array'],
            'status' => ['sometimes', 'string', 'in:completed,cancelled,rescheduled'],
        ]);

        $status = $validated['status'] ?? null;

        if ($status === 'completed') {
            $interview->markAsCompleted();
            $interview->application->update([
                'status' => Application::STATUS_INTERVIEW_COMPLETED,
                'interview_completed_at' => now(),
            ]);
        } elseif ($status === 'cancelled') {
            $interview->markAsCancelled();
        } else {
            $interview->update($validated);

            if (isset($validated['scheduled_at'])) {
                $interview->application->update([
                    'interview_scheduled_at' => $validated['scheduled_at'],
                ]);
            }
        }

        return back()->with('status', 'Interview updated successfully.');
    }

    public function destroy(Interview $interview): RedirectResponse
    {
        $interview->delete();

        return back()->with('status', 'Interview deleted.');
    }
}
