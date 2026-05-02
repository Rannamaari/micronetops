<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Asset History</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $asset->asset_code }} · {{ $asset->name }}</p>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Back to Assets</a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="mb-8">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Asset Activity</h3>
                        <div class="space-y-3">
                            @forelse($events as $event)
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                @switch($event->event_type)
                                                    @case(\App\Models\FixedAssetEvent::TYPE_CREATED)
                                                        Asset Created
                                                        @break
                                                    @case(\App\Models\FixedAssetEvent::TYPE_ASSIGNED)
                                                        Assigned
                                                        @break
                                                    @case(\App\Models\FixedAssetEvent::TYPE_RETURNED)
                                                        Returned
                                                        @break
                                                    @case(\App\Models\FixedAssetEvent::TYPE_STATUS_CHANGED)
                                                        Status Changed
                                                        @break
                                                    @case(\App\Models\FixedAssetEvent::TYPE_CONDITION_CHANGED)
                                                        Condition Changed
                                                        @break
                                                    @default
                                                        Details Updated
                                                @endswitch
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $event->event_at->format('d M Y h:i A') }} · {{ $event->performedBy?->name ?? 'System' }}
                                            </div>
                                        </div>
                                        <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                                            @if($event->old_status || $event->new_status)
                                                <div>Status: {{ $event->old_status ?: '-' }} → {{ $event->new_status ?: '-' }}</div>
                                            @endif
                                            @if($event->old_condition || $event->new_condition)
                                                <div>Condition: {{ $event->old_condition ?: '-' }} → {{ $event->new_condition ?: '-' }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @if($event->notes)
                                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $event->notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 dark:text-gray-400">No asset activity recorded yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Assignment History</h3>
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Staff Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Returned Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Condition Given</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Condition Returned</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned By</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($assignments as $assignment)
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $assignment->staff?->name ?? 'Unknown' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->assigned_at->format('d M Y h:i A') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->returned_at?->format('d M Y h:i A') ?? 'Not returned' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->condition_on_assign }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->condition_on_return ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->assignedBy?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $assignment->notes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No handover history yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-3">
                        @forelse($assignments as $assignment)
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-700/30 p-4 space-y-2">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $assignment->staff?->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Assigned {{ $assignment->assigned_at->format('d M Y h:i A') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Returned {{ $assignment->returned_at?->format('d M Y h:i A') ?? 'Not returned' }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-300">Given: {{ $assignment->condition_on_assign }} · Returned: {{ $assignment->condition_on_return ?? '-' }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-300">Assigned by {{ $assignment->assignedBy?->name ?? '-' }}</div>
                                @if($assignment->notes)
                                    <div class="text-xs text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $assignment->notes }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No handover history yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
