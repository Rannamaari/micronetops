<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Account Transfers') }}
            </h2>
            <a href="{{ route('accounts.transfers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">New Transfer</a>
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transfers as $transfer)
                                    <tr>
                                        <td class="px-4 py-2">{{ $transfer->occurred_at?->toDateString() }}</td>
                                        <td class="px-4 py-2">{{ $transfer->fromAccount?->name }}</td>
                                        <td class="px-4 py-2">{{ $transfer->toAccount?->name }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($transfer->amount, 2) }}</td>
                                        <td class="px-4 py-2">{{ $transfer->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No transfers yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transfers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
