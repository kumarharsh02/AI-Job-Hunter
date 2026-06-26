<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Dashboard
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500">{{ $totalJobs }} jobs tracked</span>
                <button type="button" onclick="document.getElementById('addJobModal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Job
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('dashboard.partials.status-cards', ['statusCounts' => $statusCounts])

            @if(session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($upcomingInterviews->isNotEmpty() || $staleApplications->isNotEmpty())
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if($upcomingInterviews->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-amber-50/50">
                                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                                    Upcoming Interviews
                                </h3>
                                <span class="text-xs text-slate-400">{{ $upcomingInterviews->count() }} upcoming</span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach($upcomingInterviews as $interview)
                                    <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-slate-900 truncate">
                                                {{ $interview->application->jobListing->title ?? 'Unknown' }}
                                                <span class="text-slate-400 font-normal">at {{ $interview->application->jobListing->company_name ?? 'Unknown' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                    {{ $interview->type_label }}
                                                </span>
                                                @if($interview->location)
                                                    <span class="text-xs text-slate-400">{{ $interview->location }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right ml-4 flex-shrink-0">
                                            <div class="text-sm font-medium text-slate-900">
                                                {{ $interview->scheduled_at->format('M j') }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $interview->scheduled_at->format('g:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($staleApplications->isNotEmpty())
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-orange-50/50">
                                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                    <span class="inline-block w-2 h-2 rounded-full bg-orange-400"></span>
                                    Follow-up Needed
                                </h3>
                                <span class="text-xs text-slate-400">{{ $staleApplications->count() }} stale</span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @foreach($staleApplications as $application)
                                    <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-slate-900 truncate">
                                                {{ $application->jobListing->title ?? 'Unknown' }}
                                                <span class="text-slate-400 font-normal">at {{ $application->jobListing->company_name ?? 'Unknown' }}</span>
                                            </div>
                                            <div class="text-xs text-orange-600 mt-0.5">
                                                Applied {{ $application->applied_at ? $application->applied_at->diffForHumans() : 'unknown' }}
                                            </div>
                                        </div>
                                        <div class="text-right ml-4 flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                                {{ $application->applied_at ? $application->applied_at->diffInDays(now()) . 'd' : '?' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <h3 class="text-lg font-semibold text-slate-900">Job Applications</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                            <select name="status" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\Application::statuses() as $value => $label)
                                    <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select name="source" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="">All Sources</option>
                                <option value="manual" {{ $sourceFilter === 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="linkedin" {{ $sourceFilter === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                <option value="naukri" {{ $sourceFilter === 'naukri' ? 'selected' : '' }}>Naukri</option>
                                <option value="indeed" {{ $sourceFilter === 'indeed' ? 'selected' : '' }}>Indeed</option>
                                <option value="wellfound" {{ $sourceFilter === 'wellfound' ? 'selected' : '' }}>Wellfound</option>
                                <option value="other" {{ $sourceFilter === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @if($statusFilter || $sourceFilter)
                                <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Match</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Job / Company</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Added</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($jobListings as $job)
                                @include('dashboard.partials.job-row', ['job' => $job])
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        No jobs found. Click "Add Job" to manually add one, or use the Chrome Extension.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($jobListings->hasPages())
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $jobListings->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('dashboard.partials.status-modal')
    @include('dashboard.partials.add-job-modal')
</x-app-layout>