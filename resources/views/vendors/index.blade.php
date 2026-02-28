<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Vendors') }}
            </h2>
            <a href="{{ route('vendors.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Add Vendor
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" class="flex gap-3 mb-6">
                        <input name="search" value="{{ $search }}" class="w-full rounded border-gray-300" placeholder="Search by name, phone, contact">
                        <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Search</button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($vendors as $vendor)
                                    <tr>
                                        <td class="px-4 py-2">{{ $vendor->name }}</td>
                                        <td class="px-4 py-2">{{ $vendor->phone }}</td>
                                        <td class="px-4 py-2">{{ $vendor->contact_name ?? '—' }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs {{ $vendor->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('vendors.edit', $vendor) }}" class="text-blue-600 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No vendors found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $vendors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
