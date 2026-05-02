<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tools Currently With Staff</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All tools that are currently out with staff members.</p>
            </div>
            <a href="{{ route('fixed-assets.index') }}" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Back to Assets</a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    @forelse($groupedAssignments as $staffName => $assignments)
                        <div class="@if(!$loop->first) mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 @endif">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $staffName }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $assignments->count() }} tool(s) currently assigned</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                @foreach($assignments as $assignment)
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $assignment->asset?->asset_code }}</div>
                                                <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $assignment->asset?->name }}</div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $assignment->asset?->category ?: 'No category' }}</div>
                                            </div>
                                            <a href="{{ route('fixed-assets.history', $assignment->asset) }}" class="text-xs text-indigo-600 hover:underline">History</a>
                                        </div>
                                        <div class="mt-3 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                                            <div>Assigned: {{ $assignment->assigned_at->format('d M Y h:i A') }}</div>
                                            <div>Condition: {{ $assignment->condition_on_assign }}</div>
                                            <div>Assigned by: {{ $assignment->assignedBy?->name ?? '-' }}</div>
                                            @if($assignment->notes)
                                                <div class="pt-2 text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $assignment->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            No tools are currently assigned to staff.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
