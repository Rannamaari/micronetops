<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Add Fixed Asset</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('fixed-assets.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('fixed-assets._form')
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('fixed-assets.index') }}" class="text-sm text-gray-600 hover:underline dark:text-gray-300">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Save Asset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
