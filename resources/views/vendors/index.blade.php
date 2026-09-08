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
                    @if(session('success'))
                        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="GET" class="flex gap-3 mb-6">
                        <input name="search" value="{{ $search }}" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Search by name, phone, contact, GST number">
                        <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Search</button>
                    </form>

                    <div class="mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-900/20 dark:text-blue-200">
                        GST-registered vendors need a GST number before their tax invoices can be recorded as GST expenses. Use <strong>Edit</strong> to add or update a vendor's GST number.
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">GST Number (TIN)</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($vendors as $vendor)
                                    <tr>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('vendors.show', $vendor) }}" class="font-semibold text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400">
                                                {{ $vendor->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2">{{ $vendor->phone }}</td>
                                        <td class="px-4 py-2">{{ $vendor->contact_name ?? '—' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            @if($vendor->gst_number)
                                                <span class="inline-flex rounded-md bg-blue-50 px-2.5 py-1 font-mono text-sm font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                                                    {{ $vendor->gst_number }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400 dark:text-gray-500">Not added</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs {{ $vendor->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('vendors.show', $vendor) }}" class="text-gray-600 hover:underline dark:text-gray-300">View</a>
                                            <a href="{{ route('vendors.edit', $vendor) }}" class="ml-3 text-blue-600 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No vendors found.</td>
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
