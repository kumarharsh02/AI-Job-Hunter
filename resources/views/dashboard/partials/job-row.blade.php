@php
    $application = $job->application;
    $statusColor = $application ? $application->status_color : 'gray';
    $statusLabel = $application ? $application->status_label : 'New';
@endphp

<tr class="hover:bg-slate-50/50 transition-colors">
    <td class="px-6 py-4 whitespace-nowrap">
        @if($job->match_score !== null)
            <span class="inline-flex items-center justify-center w-11 h-11 rounded-full text-xs font-bold
                {{ $job->match_badge_color === 'green' ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' :
                   ($job->match_badge_color === 'yellow' ? 'bg-amber-100 text-amber-700 ring-1 ring-amber-200' :
                   ($job->match_badge_color === 'orange' ? 'bg-orange-100 text-orange-700 ring-1 ring-orange-200' : 'bg-red-100 text-red-700 ring-1 ring-red-200')) }}">
                {{ number_format((float) $job->match_score, 0) }}%
            </span>
        @else
            <span class="inline-flex items-center justify-center w-11 h-11 rounded-full text-xs bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                &mdash;
            </span>
        @endif
    </td>

    <td class="px-6 py-4">
        <div class="flex flex-col">
            <a href="{{ $job->source_url }}" target="_blank" rel="noopener noreferrer"
               class="text-sm font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                {{ Str::limit($job->title, 40) }}
            </a>
            <span class="text-sm text-slate-500">{{ Str::limit($job->company_name, 30) }}</span>
            @if($job->location)
                <span class="text-xs text-slate-400">{{ $job->location }} &middot; {{ $job->work_mode }}</span>
            @endif
        </div>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            {{ $job->source_type === 'linkedin' ? 'bg-blue-100 text-blue-800' :
               ($job->source_type === 'naukri' ? 'bg-purple-100 text-purple-800' :
               ($job->source_type === 'indeed' ? 'bg-teal-100 text-teal-800' :
               ($job->source_type === 'wellfound' ? 'bg-orange-100 text-orange-800' :
               ($job->source_type === 'manual' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800')))) }}">
            {{ ucfirst($job->source_type) }}
        </span>
    </td>

    <td class="px-6 py-4 whitespace-nowrap">
        @if($application)
            <button type="button"
                    onclick="openStatusModal({{ $application->id }}, '{{ $application->status }}')"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-all
                        {{ $statusColor === 'green' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' :
                           ($statusColor === 'blue' ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' :
                           ($statusColor === 'red' ? 'bg-red-100 text-red-800 hover:bg-red-200' :
                           ($statusColor === 'yellow' ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' :
                           ($statusColor === 'indigo' ? 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200' : 'bg-slate-100 text-slate-800 hover:bg-slate-200')))) }}">
                    {{ $statusLabel }}
            </button>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-500">
                No Application
            </span>
        @endif
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
        {{ $job->salary_display }}
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
        {{ $job->created_at->format('M j') }}
    </td>

    <td class="px-6 py-4 whitespace-nowrap text-right">
        <div class="flex items-center justify-end gap-2">
            <form method="POST" action="{{ route('jobs.generate-cover-letter', $job->id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        title="Generate Cover Letter">
                    AI Cover Letter
                </button>
            </form>
            @if($application)
                <button type="button"
                        onclick="openStatusModal({{ $application->id }}, '{{ $application->status }}')"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors shadow-sm"
                        title="Update Status">
                    Update Status
                </button>
            @endif
        </div>
    </td>
</tr>