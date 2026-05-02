<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Assign Asset</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-5 rounded-xl bg-gray-50 dark:bg-gray-700/30 p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Asset</div>
                        <div class="mt-1 text-lg font-semibold">{{ $asset->asset_code }} · {{ $asset->name }}</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $asset->category ?: 'No category' }}</div>
                    </div>

                    <form method="POST" action="{{ route('fixed-assets.assign.store', $asset) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Staff</label>
                            <select name="staff_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="">Choose staff</option>
                                @foreach($staffOptions as $staff)
                                    <option value="{{ $staff->id }}" @selected((string) old('staff_id') === (string) $staff->id)>{{ $staff->name }} ({{ str($staff->role)->replace('_', ' ')->title() }})</option>
                                @endforeach
                            </select>
                            @error('staff_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned Date</label>
                                <input type="datetime-local" name="assigned_at" value="{{ old('assigned_at', now()->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                @error('assigned_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition When Given</label>
                                <select name="condition_on_assign" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                    @foreach($conditionOptions as $condition)
                                        <option value="{{ $condition }}" @selected(old('condition_on_assign', $asset->condition) === $condition)>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                @error('condition_on_assign') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" rows="4" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Optional notes about the handover">{{ old('notes') }}</textarea>
                            @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('fixed-assets.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Assign Asset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
