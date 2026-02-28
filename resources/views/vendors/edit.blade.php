<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Vendor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('vendors.update', $vendor) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-sm font-medium">Vendor Name</label>
                            <input name="name" class="mt-1 w-full rounded border-gray-300" value="{{ $vendor->name }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Phone Number</label>
                            <input name="phone" class="mt-1 w-full rounded border-gray-300" value="{{ $vendor->phone }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Contact Name</label>
                            <input name="contact_name" class="mt-1 w-full rounded border-gray-300" value="{{ $vendor->contact_name }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Address</label>
                            <input name="address" class="mt-1 w-full rounded border-gray-300" value="{{ $vendor->address }}">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked($vendor->is_active)>
                            <label class="text-sm">Active</label>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('vendors.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Vendor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
