<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $account->name }}
            </h2>
            <a href="{{ route('accounts.edit', $account) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Edit</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Type</div>
                        <div class="font-medium">{{ ucfirst($account->type) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Balance</div>
                        <div class="font-semibold text-lg">{{ number_format($account->balance, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="font-medium">{{ $account->is_active ? 'Active' : 'Inactive' }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Adjust Balance</h3>
                    <form method="POST" action="{{ route('accounts.adjust', $account) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium">Amount (+/-)</label>
                            <input type="number" step="0.01" name="amount" class="mt-1 w-full rounded border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium">Date</label>
                            <input type="date" name="occurred_at" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium">Description</label>
                            <input name="description" class="mt-1 w-full rounded border-gray-300">
                        </div>
                        <div class="flex items-end">
                            <button class="w-full px-4 py-2 bg-gray-900 text-white rounded-lg">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transactions as $tx)
                                    <tr>
                                        <td class="px-4 py-2">{{ $tx->occurred_at?->toDateString() }}</td>
                                        <td class="px-4 py-2">{{ str_replace('_', ' ', ucfirst($tx->type)) }}</td>
                                        <td class="px-4 py-2">{{ $tx->description ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right {{ $tx->amount < 0 ? 'text-red-600' : 'text-green-700' }}">
                                            {{ number_format($tx->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No transactions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
