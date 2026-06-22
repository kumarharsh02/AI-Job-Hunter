<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoverLetterRequest;
use App\Jobs\GenerateAiCoverLetter;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\Resume;

class CoverLetterController extends Controller
{
    public function store(StoreCoverLetterRequest $request)
    {
        $validated = $request->validated();

        $jobListing = JobListing::findOrFail($validated['job_listing_id']);
        $resume = Resume::findOrFail($validated['resume_id']);

        $application = Application::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'job_listing_id' => $jobListing->id,
            ],
            [
                'resume_id' => $resume->id,
                'status' => 'draft',
            ],
        );

        GenerateAiCoverLetter::dispatch(
            jobListing: $jobListing,
            resume: $resume,
            tone: $validated['tone'] ?? 'professional',
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Cover letter generation has been queued. It will appear here shortly.');
    }
}