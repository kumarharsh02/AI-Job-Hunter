<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use App\Models\JobListing;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $statusFilter = $request->query('status');
        $sourceFilter = $request->query('source');

        $jobListings = JobListing::where('user_id', $user->id)
            ->with(['applications' => function ($query) {
                $query->with('coverLetters')->latest();
            }])
            ->when($statusFilter, function ($query, $status) {
                $query->whereHas('applications', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })
            ->when($sourceFilter, function ($query, $source) {
                $query->where('source_type', $source);
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->through(function ($job) {
                $job->application = $job->applications->first();
                $job->latest_cover_letter = $job->application?->coverLetters->first();

                return $job;
            });

        $statusCounts = Application::where('user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalJobs = JobListing::where('user_id', $user->id)->count();

        $upcomingInterviews = Interview::where('user_id', $user->id)
            ->with(['application.jobListing'])
            ->upcoming()
            ->limit(5)
            ->get();

        $staleApplications = Application::where('user_id', $user->id)
            ->staleApplied(7)
            ->with('jobListing')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'jobListings',
            'statusCounts',
            'totalJobs',
            'statusFilter',
            'sourceFilter',
            'upcomingInterviews',
            'staleApplications',
        ));
    }
}
