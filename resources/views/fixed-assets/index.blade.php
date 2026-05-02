<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Fixed Assets</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('fixed-assets.current-custody') }}" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Tools With Staff</a>
                <a href="{{ route('fixed-assets.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Add Asset</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_180px_auto] gap-3 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input name="search" value="{{ $search }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm h-10" placeholder="Asset code, name, brand...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm h-10">
                                <option value="">All</option>
                                @foreach($statusOptions as $option)
                                    <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button class="h-10 px-4 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
                            <a href="{{ route('fixed-assets.index') }}" class="h-10 px-4 inline-flex items-center rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Reset</a>
                        </div>
                    </form>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Photo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Asset Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Condition</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Current Holder</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($assets as $asset)
                                    @php $current = $asset->currentAssignment; @endphp
                                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-4 py-3 text-sm">
                                            @if($asset->photo_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($asset->photo_path) }}" alt="{{ $asset->name }}" class="h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                            @else
                                                <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 border border-dashed border-gray-300 dark:border-gray-600"></div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $asset->asset_code }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $asset->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $asset->categoryEntity?->name ?? $asset->category ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $asset->condition }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                                {{ $asset->status === 'Available' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                                {{ $asset->status === 'Assigned' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                                {{ $asset->status === 'Under Repair' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : '' }}
                                                {{ $asset->status === 'Sold' ? 'bg-slate-100 text-slate-800 dark:bg-slate-900/40 dark:text-slate-300' : '' }}
                                                {{ in_array($asset->status, ['Retired', 'Lost'], true) ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : '' }}">
                                                {{ $asset->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $current?->staff?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $current?->assigned_at?->format('d M Y h:i A') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <div class="flex justify-end gap-2 flex-wrap">
                                                <a href="{{ route('fixed-assets.edit', $asset) }}" class="text-gray-600 hover:underline dark:text-gray-300">Edit</a>
                                                @if($asset->status === \App\Models\FixedAsset::STATUS_AVAILABLE)
                                                    <a href="{{ route('fixed-assets.assign.create', $asset) }}" class="text-blue-600 hover:underline">Assign</a>
                                                @endif
                                                @if($asset->status === \App\Models\FixedAsset::STATUS_ASSIGNED && $current)
                                                    <a href="{{ route('fixed-assets.return.create', $asset) }}" class="text-emerald-600 hover:underline">Mark Returned</a>
                                                @endif
                                                <a href="{{ route('fixed-assets.history', $asset) }}" class="text-indigo-600 hover:underline">History</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No assets found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden space-y-3">
                        @forelse($assets as $asset)
                            @php $current = $asset->currentAssignment; @endphp
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-700/30 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $asset->asset_code }}</div>
                                        <div class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $asset->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $asset->categoryEntity?->name ?? $asset->category ?: 'No category' }}</div>
                                    </div>
                                    <div class="text-right">
                                        @if($asset->photo_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($asset->photo_path) }}" alt="{{ $asset->name }}" class="ml-auto h-12 w-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700 mb-2">
                                        @endif
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->condition }}</div>
                                        <div class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $asset->status }}</div>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-600 dark:text-gray-300">
                                    Holder: {{ $current?->staff?->name ?? '-' }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Assigned: {{ $current?->assigned_at?->format('d M Y h:i A') ?? '-' }}
                                </div>
                                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                    <a href="{{ route('fixed-assets.edit', $asset) }}" class="text-gray-700 dark:text-gray-200 hover:underline">Edit</a>
                                    @if($asset->status === \App\Models\FixedAsset::STATUS_AVAILABLE)
                                        <a href="{{ route('fixed-assets.assign.create', $asset) }}" class="text-blue-600 hover:underline">Assign</a>
                                    @endif
                                    @if($asset->status === \App\Models\FixedAsset::STATUS_ASSIGNED && $current)
                                        <a href="{{ route('fixed-assets.return.create', $asset) }}" class="text-emerald-600 hover:underline">Return</a>
                                    @endif
                                    <a href="{{ route('fixed-assets.history', $asset) }}" class="text-indigo-600 hover:underline">History</a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">No assets found.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $assets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
