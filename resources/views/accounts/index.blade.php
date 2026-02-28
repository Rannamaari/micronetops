<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Accounts') }}
            </h2>
            <a href="{{ route('accounts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Account</a>
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td class="px-4 py-2">{{ $account->name }}</td>
                                        <td class="px-4 py-2">{{ ucfirst($account->type) }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($account->balance, 2) }}</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('accounts.show', $account) }}" class="text-blue-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $accounts->links() }}
                    </div>

                    <div class="mt-6 text-sm text-gray-500">
                        <a href="{{ route('accounts.transfers.index') }}" class="text-blue-600 hover:underline">View Transfers</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
