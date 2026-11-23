{{-- One job in a list. Expects $job (decorated with deskAlerts) --}}
@php
    $statuses = \App\Models\Job::getStatuses();
    $units = \App\Services\JobDesk::unitLabels();
    $promised = $job->promisedAt();
    $hasDanger = collect($job->deskAlerts)->contains('level', 'danger');
@endphp
<a href="{{ route('desk.show', $job) }}"
   class="block bg-white rounded-xl shadow-sm border-l-4 {{ $hasDanger ? 'border-red-500' : (count($job->deskAlerts) ? 'border-amber-400' : 'border-gray-200') }} p-4 hover:bg-gray-50 active:bg-gray-100">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-xs text-gray-500">
                #{{ $job->id }} · {{ __($units[$job->businessUnit()] ?? $job->job_type) }}
            </div>
            <div class="font-semibold text-gray-900 truncate">
                {{ $job->title ?: __('Job') . ' #' . $job->id }}
            </div>
            <div class="text-sm text-gray-600 truncate">
                {{ $job->customer_name }}@if($job->location) · {{ $job->location }}@endif
            </div>
        </div>
        <div class="text-right flex-shrink-0">
            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $job->status_color }}">
                {{ __($statuses[$job->status] ?? $job->status) }}
            </span>
            @if($promised)
                <div class="mt-1 text-xs {{ $job->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                    {{ $promised->translatedFormat('D j M, g:i A') }}
                </div>
            @endif
        </div>
    </div>
    <div class="mt-2 text-xs text-gray-600">
        {{ __('Assigned to') }}:
        @forelse($job->assignees as $person)
            <span class="font-semibold text-gray-800">{{ $person->name }}</span>@if(!$person->pivot->accepted_at)<span class="text-amber-700"> ({{ __('not accepted') }})</span>@endif{{ !$loop->last ? ',' : '' }}
        @empty
            <span class="font-semibold text-red-600">{{ __('Nobody') }}</span>
        @endforelse
    </div>
    @if(count($job->deskAlerts))
        <div class="mt-2">
            @include('desk.partials.alerts', ['alerts' => $job->deskAlerts])
        </div>
    @endif
</a>
