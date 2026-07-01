<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobListingRequest;
use App\Jobs\AnalyzeResumeMatch;
use App\Jobs\GenerateAiCoverLetter;
use App\Models\Application;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function store(StoreJobListingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $jobListing = JobListing::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'company_name' => $validated['company_name'],
            'source_url' => $validated['source_url'],
            'description' => $validated['description'],
            'location' => $validated['location'] ?? null,
            'work_mode' => $validated['work_mode'] ?? 'onsite',
            'employment_type' => $validated['employment_type'] ?? 'full-time',
            'salary_min' => $validated['salary_min'] ?? null,
            'salary_max' => $validated['salary_max'] ?? null,
            'currency' => $validated['currency'] ?? 'INR',
            'source_type' => 'manual',
            'source_id' => null,
        ]);

        $resume = $user->activeResume();
        if ($resume !== null) {
            AnalyzeResumeMatch::dispatch(jobListing: $jobListing, resume: $resume);
        }

        return back()->with('status', 'Job added successfully.'.($resume ? ' Resume match analysis has been queued.' : ' Upload a resume to get AI match analysis.'));
    }

    public function generateCoverLetter(Request $request, JobListing $jobListing): RedirectResponse
    {
        $user = $request->user();

        $application = $jobListing->applications()
            ->where('user_id', $user->id)
            ->first();

        if ($application === null) {
            $application = Application::create([
                'user_id' => $user->id,
                'job_listing_id' => $jobListing->id,
                'status' => 'draft',
            ]);
        }

        $pendingCoverLetter = $application->coverLetters()
            ->whereNull('content')
            ->exists();

        if ($pendingCoverLetter) {
            return back()->with('error', 'A cover letter is already being generated for this job. Please wait for it to complete.');
        }

        $resume = $user->activeResume();

        if ($resume === null) {
            return back()->with('error', 'No active resume found. Please upload and activate a resume first.');
        }

        GenerateAiCoverLetter::dispatch(
            jobListing: $jobListing,
            resume: $resume,
        );

        return back()->with('status', 'Cover letter generation has been queued. It will appear here shortly.');
    }
}
