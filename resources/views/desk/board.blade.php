@include('desk.partials.head')

@php
    $alertLabels = \App\Services\JobDesk::alertLabels();
    $units = \App\Services\JobDesk::unitLabels();
    $statuses = \App\Models\Job::getStatuses();
    $current = request('alert');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('desk.index') }}" class="text-sm text-indigo-600 font-medium">&larr; {{ __('Job Desk') }}</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Admin board') }}</h2>
            </div>
            @include('desk.partials.lang')
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 space-y-5">

            @if($escalated)
                <div class="rounded-xl border-2 border-red-400 bg-red-50 px-4 py-3 text-red-800 font-semibold">
                    {{ trans_choice(':count job needs you now (escalated)|:count jobs need you now (escalated)', $escalated, ['count' => $escalated]) }}
                </div>
            @endif

            {{-- Alert counters (click to filter) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                @foreach($alertLabels as $type => $label)
                    <a href="{{ request()->fullUrlWithQuery(['alert' => $current === $type ? null : $type]) }}"
                       class="rounded-xl p-3 border-2 {{ $current === $type ? 'border-indigo-600 bg-indigo-50' : 'border-transparent bg-white' }} shadow-sm">
                        <div class="text-2xl font-bold {{ in_array($type, ['call_customer', 'overdue']) && $counts[$type] ? 'text-red-600' : 'text-gray-900' }}">{{ $counts[$type] }}</div>
                        <div class="text-xs text-gray-600">{{ __($label) }}</div>
                    </a>
                @endforeach
            </div>

            {{-- Filters --}}
            <form method="GET" class="flex flex-wrap gap-2 items-end">
                @if($current)
                    <input type="hidden" name="alert" value="{{ $current }}">
                @endif
                <div>
                    <label class="block text-xs text-gray-500">{{ __('Staff') }}</label>
                    <select name="staff" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('Everyone') }}</option>
                        @foreach($staff as $person)
                            <option value="{{ $person->id }}" @selected((int) request('staff') === $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500">{{ __('Business') }}</label>
                    <select name="unit" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach($units as $key => $label)
                            <option value="{{ $key }}" @selected(request('unit') === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request()->hasAny(['alert', 'staff', 'unit']))
                    <a href="{{ route('desk.board') }}" class="text-sm text-indigo-600 py-2">{{ __('Clear filters') }}</a>
                @endif
            </form>

            {{-- Jobs: every open job, problems first --}}
            <div class="text-sm text-gray-600">{{ trans_choice(':count open job|:count open jobs', $jobs->count(), ['count' => $jobs->count()]) }}</div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 text-xs font-semibold text-gray-500 border-b">
                    <div class="col-span-4">{{ __('Job') }}</div>
                    <div class="col-span-2">{{ __('Assigned to') }}</div>
                    <div class="col-span-2">{{ __('Promised time') }}</div>
                    <div class="col-span-4">{{ __('Alerts') }}</div>
                </div>
                @forelse($jobs as $job)
                    <a href="{{ route('desk.show', $job) }}" class="grid grid-cols-1 md:grid-cols-12 gap-1 md:gap-3 px-4 py-3 border-b last:border-0 hover:bg-gray-50">
                        <div class="md:col-span-4 min-w-0">
                            <div class="text-xs text-gray-500">#{{ $job->id }} · {{ __($units[$job->businessUnit()] ?? $job->job_type) }} · {{ __($statuses[$job->status] ?? $job->status) }}</div>
                            <div class="font-semibold text-gray-900 truncate">{{ $job->title ?: __('Job') . ' #' . $job->id }}</div>
                            <div class="text-sm text-gray-600 truncate">{{ $job->customer_name }} {{ $job->customer_phone ? '· ' . $job->customer_phone : '' }}</div>
                        </div>
                        <div class="md:col-span-2 text-sm text-gray-700">
                            @forelse($job->assignees as $person)
                                <div>{{ $person->name }} @unless($person->pivot->accepted_at)<span class="text-amber-600">({{ __('not accepted') }})</span>@endunless</div>
                            @empty
                                <span class="text-red-600">{{ __('Nobody') }}</span>
                            @endforelse
                        </div>
                        <div class="md:col-span-2 text-sm {{ $job->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                            {{ $job->promisedAt()?->translatedFormat('D j M, g:i A') ?? '—' }}
                        </div>
                        <div class="md:col-span-4">
                            @include('desk.partials.alerts', ['alerts' => $job->deskAlerts])
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">{{ __('No open jobs.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
