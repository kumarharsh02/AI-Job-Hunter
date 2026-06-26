<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    @foreach(\App\Models\Application::statuses() as $statusValue => $statusLabel)
        @php $count = $statusCounts[$statusValue] ?? 0 @endphp
        @php
            $bgClass = match(\App\Models\Application::statusColors()[$statusValue]) {
                'green' => 'bg-emerald-50 border-emerald-200',
                'blue' => 'bg-blue-50 border-blue-200',
                'red' => 'bg-red-50 border-red-200',
                'yellow' => 'bg-amber-50 border-amber-200',
                'indigo' => 'bg-indigo-50 border-indigo-200',
                default => 'bg-slate-50 border-slate-200'
            };
            $textClass = match(\App\Models\Application::statusColors()[$statusValue]) {
                'green' => 'text-emerald-700',
                'blue' => 'text-blue-700',
                'red' => 'text-red-700',
                'yellow' => 'text-amber-700',
                'indigo' => 'text-indigo-700',
                default => 'text-slate-700'
            };
            $labelClass = match(\App\Models\Application::statusColors()[$statusValue]) {
                'green' => 'text-emerald-600',
                'blue' => 'text-blue-600',
                'red' => 'text-red-600',
                'yellow' => 'text-amber-600',
                'indigo' => 'text-indigo-600',
                default => 'text-slate-600'
            };
        @endphp
        <div class="{{ $bgClass }} border rounded-xl p-4 text-center transition-all hover:shadow-sm">
            <div class="text-2xl font-bold {{ $textClass }}">{{ $count }}</div>
            <div class="text-xs {{ $labelClass }} mt-1 font-medium">{{ $statusLabel }}</div>
        </div>
    @endforeach
</div>