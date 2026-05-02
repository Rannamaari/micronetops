<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mark Asset Returned</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-5 rounded-xl bg-gray-50 dark:bg-gray-700/30 p-4 space-y-1">
                        <div class="text-lg font-semibold">{{ $asset->asset_code }} · {{ $asset->name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">Currently with {{ $assignment->staff?->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Given on {{ $assignment->assigned_at->format('d M Y h:i A') }} by {{ $assignment->assignedBy?->name ?? 'Unknown' }}</div>
                    </div>

                    <form method="POST" action="{{ route('fixed-assets.return.store', $asset) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Return Date</label>
                                <input type="datetime-local" name="returned_at" value="{{ old('returned_at', now()->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                @error('returned_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition When Returned</label>
                                <select name="condition_on_return" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                    @foreach($conditionOptions as $condition)
                                        <option value="{{ $condition }}" @selected(old('condition_on_return', $asset->condition) === $condition)>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                @error('condition_on_return') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" rows="4" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Optional notes about the return">{{ old('notes') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Any return notes will be added to the assignment history.</p>
                            @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('fixed-assets.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
                            <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">Mark Returned</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
