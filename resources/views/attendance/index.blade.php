<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Attendance</div>
                <h2 ="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Month/Year Selector --}}
            <div class="mb-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Month</label>
                        <select name="month" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                        <select name="year" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-sm">
                        Change Month
                    </button>
                </form>
            </div>

            {{-- Status Badge --}}
            @if($allMarked)
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-green-800 dark:text-green-200">Attendance marked for all employees - Payroll can be processed</span>
                    </div>
                </div>
            @else
                <div class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Attendance not fully marked - Complete attendance to process payroll</span>
                    </div>
                </div>
            @endif

            {{-- Legend --}}
            <div class="mb-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Legend:</h3>
                <div class="flex flex-wrap gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-green-500 bg-green-100"></div>
                        <span class="text-gray-600 dark:text-gray-400">✓ Present (check to mark)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-red-50 dark:bg-red-900/10 flex items-center justify-center text-red-600 font-medium">H</div>
                        <span class="text-gray-600 dark:text-gray-400">Friday (Holiday)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-blue-50 dark:bg-blue-900/10 flex items-center justify-center text-blue-600 font-medium">L</div>
                        <span class="text-gray-600 dark:text-gray-400">On Leave (Approved)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">-</div>
                        <span class="text-gray-600 dark:text-gray-400">Before Hire Date</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded border-2 border-gray-300"></div>
                        <span class="text-gray-600 dark:text-gray-400">Absent (unchecked = no pay)</span>
                    </div>
                </div>
            </div>

            @if($employees->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No active employees found.</p>
                </div>
            @else
                <form method="POST" action="{{ route('attendance.store') }}" x-data="attendanceForm()">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="sticky left-0 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    @foreach($days as $day)
                                        <th class="px-2 py-3 text-center text-xs font-medium {{ $day['isFriday'] ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }} uppercase">
                                            <div>{{ $day['dayName'] }}</div>
                                            <div class="text-lg">{{ $day['day'] }}</div>
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                        Summary
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($employees as $employee)
                                    @php
                                        $employeeAttendance = $attendanceRecords->get($employee->id, collect());
                                        $employeeLeaves = $approvedLeaves->get($employee->id, collect());
                                        $hireDate = $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date) : null;
                                    @endphp
                                    <tr>
                                        <td class="sticky left-0 bg-white dark:bg-gray-800 px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div>{{ $employee->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employee_number }}</div>
                                            @if($hireDate)
                                                <div class="text-xs text-blue-600 dark:text-blue-400">Joined: {{ $hireDate->format('M d, Y') }}</div>
                                            @endif
                                        </td>
                                        @foreach($days as $day)
                                            @php
                                                $dayDate = \Carbon\Carbon::parse($day['date']);
                                                $attendance = $employeeAttendance->firstWhere('date', $dayDate);
                                                $isPresent = $attendance && $attendance->status === 'present';

                                                // Check if on leave this day
                                                $onLeave = $employeeLeaves->first(function($leave) use ($dayDate) {
                                                    return $dayDate->between(\Carbon\Carbon::parse($leave->start_date), \Carbon\Carbon::parse($leave->end_date));
                                                });

                                                // Check if before hire date
                                                $beforeHire = $hireDate && $dayDate->lt($hireDate);
                                            @endphp
                                            <td class="px-2 py-3 text-center {{ $day['isFriday'] ? 'bg-red-50 dark:bg-red-900/10' : '' }} {{ $onLeave ? 'bg-blue-50 dark:bg-blue-900/10' : '' }} {{ $beforeHire ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                                @if($beforeHire)
                                                    <span class="text-xs text-gray-400">-</span>
                                                @elseif($day['isFriday'])
                                                    <span class="text-xs text-red-600 dark:text-red-400 font-medium">H</span>
                                                @elseif($onLeave)
                                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-medium" title="{{ ucfirst($onLeave->leave_type) }} Leave">L</span>
                                                @else
                                                    <input
                                                        type="checkbox"
                                                        name="attendance[{{ $employee->id }}][{{ $day['date'] }}]"
                                                        value="present"
                                                        {{ $isPresent ? 'checked' : '' }}
                                                        class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500"
                                                    >
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-center text-sm">
                                            @php
                                                $presentDays = $employeeAttendance->where('status', 'present')->count();
                                                $absentDays = $employeeAttendance->where('status', 'absent')->count();
                                                $holidays = $employeeAttendance->where('status', 'holiday')->count();
                                                $leaveDays = $employeeAttendance->where('status', 'leave')->count();
                                            @endphp
                                            <div class="text-green-600 dark:text-green-400 font-semibold">P: {{ $presentDays }}</div>
                                            <div class="text-red-600 dark:text-red-400 font-semibold">A: {{ $absentDays }}</div>
                                            <div class="text-blue-600 dark:text-blue-400 text-xs">L: {{ $leaveDays }}</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs">H: {{ $holidays }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 flex gap-3 justify-end">
                        <form method="POST" action="{{ route('attendance.mark-all-present') }}" class="inline">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded text-sm">
                                Mark All Present
                            </button>
                        </form>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-sm">
                            Save Attendance
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function attendanceForm() {
            return {
                // Add any form-specific logic here if needed
            }
        }
    </script>
</x-app-layout>
