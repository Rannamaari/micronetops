{{-- One job in a list (compact). Expects $job (decorated with deskAlerts) --}}
@php
    $statuses = \App\Models\Job::getStatuses();
    $units = \App\Services\JobDesk::unitLabels();
    $promised = $job->promisedAt();
    $hasDanger = collect($job->deskAlerts)->contains('level', 'danger');
    $timeOnly = $timeOnly ?? false;
    // One colour per business so staff can spot their jobs at a glance.
    $dot = match ($job->businessUnit()) {
        'cool' => 'bg-sky-500',
        'moto' => 'bg-orange-500',
        'it' => 'bg-indigo-500',
        'easyfix' => 'bg-emerald-500',
        default => 'bg-gray-400',
    };
@endphp
<a href="{{ route('desk.show', $job) }}"
   class="block bg-white rounded-lg shadow-sm border-l-4 {{ $hasDanger ? 'border-red-500' : (count($job->deskAlerts) ? 'border-amber-400' : 'border-gray-200') }} px-3 py-2 hover:bg-gray-50 active:bg-gray-100">
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0 flex items-baseline gap-1.5">
            <span class="text-xs text-gray-500 flex-shrink-0">#{{ $job->id }}</span>
            <span class="text-sm font-semibold text-gray-900 truncate">{{ $job->title ?: __('Job') . ' #' . $job->id }}</span>
        </div>
        <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[11px] font-medium text-white" style="background-color: {{ $job->status_color }}">
            {{ __($statuses[$job->status] ?? $job->status) }}
        </span>
    </div>
    <div class="flex items-center justify-between gap-2 text-xs text-gray-600">
        <span class="truncate"><span class="inline-block w-2 h-2 rounded-full {{ $dot }} mr-1 align-middle"></span>{{ __($units[$job->businessUnit()] ?? $job->job_type) }} · {{ $job->customer_name }}@if($job->location) · {{ $job->location }}@endif</span>
        @if($promised)
            <span class="flex-shrink-0 {{ $job->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-700 font-semibold' }}">{{ $timeOnly ? $promised->translatedFormat('g:i A') : $promised->translatedFormat('D j M, g:i A') }}</span>
        @endif
    </div>
    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-gray-600">
        <span>
            {{ __('Assigned to') }}:
            @forelse($job->assignees as $person)
                <span class="font-semibold text-gray-800">{{ $person->name }}</span>@if(!$person->pivot->accepted_at)<span class="text-amber-700"> ({{ __('not accepted') }})</span>@endif{{ !$loop->last ? ',' : '' }}
            @empty
                <span class="font-semibold text-red-600">{{ __('Nobody') }}</span>
            @endforelse
        </span>
        @if(count($job->deskAlerts))
            @include('desk.partials.alerts', ['alerts' => $job->deskAlerts, 'compact' => true])
        @endif
    </div>
</a>
