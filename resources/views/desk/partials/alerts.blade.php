{{-- Alert badges. Expects $alerts (array from JobDesk::alertsFor) --}}
@php
    $alertLabels = \App\Services\JobDesk::alertLabels();
    $styles = [
        'danger' => 'bg-red-100 text-red-800 border-red-200',
        'warn' => 'bg-amber-100 text-amber-800 border-amber-200',
        'info' => 'bg-blue-100 text-blue-800 border-blue-200',
    ];
@endphp
@if(!empty($alerts))
    <div class="flex flex-wrap gap-1.5">
        @foreach($alerts as $alert)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-xs font-semibold {{ $styles[$alert['level']] ?? $styles['info'] }}">
                @if($alert['level'] === 'danger')
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                @endif
                {{ __($alertLabels[$alert['type']] ?? $alert['type']) }}
                @if($alert['type'] === 'call_customer' && $alert['scope'] !== 'assignee')
                    · {{ $alert['scope'] === 'admin' ? __('escalated') : __('team') }}
                @endif
                @if($alert['since'] && in_array($alert['type'], ['overdue', 'call_customer', 'not_accepted']))
                    · {{ $alert['since']->diffForHumans(null, true) }}
                @endif
            </span>
        @endforeach
    </div>
@endif
