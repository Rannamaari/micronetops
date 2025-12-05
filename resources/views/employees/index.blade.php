<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Employees') }}
            </h2>
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Employee
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

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employees..." class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    </div>
                    <div>
                        <select name="status" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="all">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        </select>
                    </div>
                    <div>
                        <select name="type" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="all">All Types</option>
                            <option value="full-time" {{ request('type') === 'full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part-time" {{ request('type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                            <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Filter
                        </button>
                        <a href="{{ route('employees.index') }}" class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Employee List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compliance Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($employees as $employee)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $employee->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->employee_number }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->phone }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $employee->position }}</div>
                                        @if($employee->department)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->department }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $employee->type === 'full-time' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $employee->type === 'part-time' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $employee->type === 'contract' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ ucfirst($employee->type) }}
                                        </span>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $employee->status === 'active' ? '✓ Active' : '✗ ' . ucfirst($employee->status) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-1 text-xs">
                                            @php $status = $employee->compliance_status; @endphp
                                            <div class="{{ $status['passport']['class'] }}">
                                                🛂 {{ $status['passport']['status'] }}
                                            </div>
                                            <div class="{{ $status['work_permit']['class'] }}">
                                                📋 {{ $status['work_permit']['status'] }}
                                            </div>
                                            <div class="{{ $status['visa']['class'] }}">
                                                ✈️ {{ $status['visa']['status'] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">View</a>
                                        <a href="{{ route('employees.edit', $employee) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 mr-3">Edit</a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No employees found. <a href="{{ route('employees.create') }}" class="text-indigo-600 hover:underline">Add your first employee</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($employees->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
