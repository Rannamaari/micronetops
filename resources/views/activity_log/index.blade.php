<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Activity Log</h2>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                        <select name="user_id" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm h-9">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Source</label>
                        <select name="source" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm h-9">
                            <option value="">All</option>
                            <option value="web" @selected(request('source') === 'web')>Web</option>
                            <option value="api" @selected(request('source') === 'api')>API (Bot)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Action</label>
                        <input type="text" name="action" value="{{ request('action') }}" placeholder="e.g. expense, sale"
                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm h-9">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm h-9">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm h-9">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 h-9 bg-gray-900 text-white rounded text-sm">Filter</button>
                        <a href="{{ route('activity-log.index') }}" class="px-3 h-9 flex items-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm">Clear</a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Events ({{ $logs->total() }})
                    </h3>
                </div>

                @if($logs->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">No activity recorded yet.</div>
                @else
                    {{-- Desktop --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Source</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($logs as $log)
                                    @php
                                        $actionColor = match(true) {
                                            str_ends_with($log->action, '.deleted') || str_ends_with($log->action, '.destroyed') => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            str_ends_with($log->action, '.created') => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            str_ends_with($log->action, '.updated') => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                        $sourceColor = $log->source === 'api'
                                            ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $log->user?->name ?? 'System' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $sourceColor }}">
                                                {{ $log->source }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $actionColor }}">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $log->description }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile --}}
                    <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($logs as $log)
                            @php
                                $actionColor = match(true) {
                                    str_ends_with($log->action, '.deleted') => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    str_ends_with($log->action, '.created') => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    str_ends_with($log->action, '.updated') => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                };
                            @endphp
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'System' }}</span>
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium {{ $actionColor }}">{{ $log->action }}</span>
                                            @if($log->source === 'api')
                                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">API</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $log->description }}</div>
                                    </div>
                                    <div class="text-xs text-gray-400 shrink-0">{{ $log->created_at->format('d M H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($logs->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
