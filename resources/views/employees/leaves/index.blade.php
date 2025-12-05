<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Leave Records</div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $employee->name }}
                    </h2>
                </div>
            </div>
            <a href="{{ route('employees.leaves.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Leave
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Leave Balance Summary --}}
            @php $leaveBalance = $employee->leave_balance; @endphp
            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Annual Leave Balance</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $leaveBalance['annual']['remaining'] }} days</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Available: {{ $leaveBalance['annual']['total_available'] }} | Used: {{ $leaveBalance['annual']['used'] }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Sick Leave Balance</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $leaveBalance['sick']['remaining'] }} days</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Total: {{ $leaveBalance['sick']['total'] }} | Used: {{ $leaveBalance['sick']['used'] }}
                    </div>
                </div>
            </div>

            {{-- Leave List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Leave Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Days</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($leaves as $leave)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $leave->leave_type === 'annual' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $leave->leave_type === 'sick' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $leave->leave_type === 'unpaid' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $leave->leave_type === 'emergency' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ in_array($leave->leave_type, ['maternity', 'paternity']) ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ ucfirst($leave->leave_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $leave->start_date->format('M d, Y') }}
                                            @if($leave->start_date->format('Y-m-d') !== $leave->end_date->format('Y-m-d'))
                                                - {{ $leave->end_date->format('M d, Y') }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $leave->start_date->format('Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $leave->days }} day(s)</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $leave->reason ?: 'No reason provided' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('employees.leaves.edit', [$employee, $leave]) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 mr-3">Edit</a>
                                        <form action="{{ route('employees.leaves.destroy', [$employee, $leave]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No leave records found. <a href="{{ route('employees.leaves.create', $employee) }}" class="text-indigo-600 hover:underline">Add the first leave record</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($leaves->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $leaves->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
