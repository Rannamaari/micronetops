<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Asset Categories</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Category</h3>
                    <form method="POST" action="{{ route('fixed-assets.categories.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                            <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 uppercase" placeholder="DRL" required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used in auto-generated asset codes.</p>
                        </div>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Save Category</button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Existing Categories</h3>
                    <div class="space-y-3">
                        @forelse($categories as $category)
                            <form method="POST" action="{{ route('fixed-assets.categories.update', $category) }}" class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_120px_120px] gap-3 items-end rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Name</label>
                                    <input name="name" value="{{ old('name.' . $category->id, $category->name) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Code</label>
                                    <input name="code" value="{{ old('code.' . $category->id, $category->code) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 uppercase" required>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $category->assets_count }} assets</div>
                                    <button class="px-3 py-2 bg-gray-900 text-white rounded-lg text-sm">Update</button>
                                </div>
                            </form>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400">No categories yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
