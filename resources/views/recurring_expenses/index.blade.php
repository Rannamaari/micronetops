<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Recurring Expenses') }}
            </h2>
            <a href="{{ route('recurring-expenses.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Add Recurring
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Frequency</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Next Due</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($recurring as $item)
                                    <tr>
                                        <td class="px-4 py-2">{{ $item->name }}</td>
                                        <td class="px-4 py-2">{{ $item->category?->name }}</td>
                                        <td class="px-4 py-2">{{ $item->vendor?->name ?? $item->vendor_name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($item->amount, 2) }}</td>
                                        <td class="px-4 py-2">{{ ucfirst($item->frequency) }}</td>
                                        <td class="px-4 py-2">{{ $item->next_due_at?->toDateString() }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('recurring-expenses.edit', $item) }}" class="text-blue-600 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No recurring expenses yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $recurring->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
