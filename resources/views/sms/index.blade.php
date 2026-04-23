<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                SMS
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8">
            @if($dryRun)
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-900">
                    Local mode: <strong>DHIRAAGU_SMS_DRY_RUN</strong> is enabled. Messages will be logged as sent without contacting Dhiraagu.
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 mb-6"
                 x-data="{ audience: '{{ old('audience', 'manual') }}' }">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Send SMS
                </div>

                <form method="POST" action="{{ route('sms.send') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Audience</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="manual"
                                           x-model="audience"
                                           {{ old('audience', 'manual') === 'manual' ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">Manual Numbers</span>
                                </label>
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="all_customers"
                                           x-model="audience"
                                           {{ old('audience') === 'all_customers' ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">All Customers</span>
                                </label>
                            </div>
                            @error('audience')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sender ID (source)</label>
                            <input id="source" name="source" type="text"
                                   value="{{ old('source', $defaultSource) }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Micronet">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">If empty, the system default will be used.</div>
                            @error('source')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div x-show="audience === 'manual'" x-cloak>
                        <label for="numbers" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Numbers</label>
                        <textarea id="numbers" name="numbers" rows="4"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Example:\n9607777777\n9608888888\nor\n7777777 / 8888888">{{ old('numbers') }}</textarea>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Separate numbers with new lines, commas, or slashes. 7-digit numbers will be prefixed with 960 automatically.
                        </div>
                        @error('numbers')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea id="content" name="content" rows="5" required
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Type your SMS message...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="submit"
                                onclick="return confirm('Send this SMS now?')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                            Send SMS
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent SMS</div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">By</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Audience</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Sent</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Failed</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Message</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recent as $row)
                            <tr class="hover:bg-indigo-50 dark:hover:bg-indigo-900/10">
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <div>{{ $row->created_at?->format('Y-m-d') }}</div>
                                    <div>{{ $row->created_at?->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $row->user?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                        {{ $row->audience === 'all_customers' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $row->audience === 'all_customers' ? 'All Customers' : 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $row->sent_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $row->failed_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="max-w-md line-clamp-2">{{ $row->content }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No SMS sent yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

