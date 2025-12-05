<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Employee: {{ $employee->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6" x-data="{ salaryUsd: {{ old('basic_salary_usd', $employee->basic_salary_usd ?? 0) }}, salaryMvr: {{ old('basic_salary', $employee->basic_salary ?? 0) }} }">
                @csrf
                @method('PATCH')

                {{-- Basic Information --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee Number <span class="text-red-500">*</span></label>
                            <input type="text" name="employee_number" value="{{ old('employee_number', $employee->employee_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                            <select name="company" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="Micro Moto Garage" {{ old('company', $employee->company) === 'Micro Moto Garage' ? 'selected' : '' }}>Micro Moto Garage</option>
                                <option value="Micro Cool" {{ old('company', $employee->company) === 'Micro Cool' ? 'selected' : '' }}>Micro Cool</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $employee->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position <span class="text-red-500">*</span></label>
                            <input type="text" name="position" value="{{ old('position', $employee->position) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                            <input type="text" name="department" value="{{ old('department', $employee->department) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type <span class="text-red-500">*</span></label>
                            <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="full-time" {{ old('type', $employee->type) === 'full-time' ? 'selected' : '' }}>Full-time</option>
                                <option value="part-time" {{ old('type', $employee->type) === 'part-time' ? 'selected' : '' }}>Part-time</option>
                                <option value="contract" {{ old('type', $employee->type) === 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                            <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hire Date <span class="text-red-500">*</span></label>
                            <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Basic Salary (USD)</label>
                            <input type="number" step="0.01" name="basic_salary_usd" value="{{ old('basic_salary_usd', $employee->basic_salary_usd) }}" x-model="salaryUsd" @input="salaryMvr = (salaryUsd * 15.42).toFixed(2)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <p class="mt-1 text-xs text-gray-500">Conversion rate: 1 USD = 15.42 MVR</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Basic Salary (MVR) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required x-model="salaryMvr" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Work Status <span class="text-red-500">*</span></label>
                            <select name="work_status" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="permanent" {{ old('work_status', $employee->work_status) === 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('work_status', $employee->work_status) === 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Work Site</label>
                            <input type="text" name="work_site" value="{{ old('work_site', $employee->work_site) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Description</label>
                            <textarea name="job_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('job_description', $employee->job_description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Personal Information --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Passport Number <span class="text-red-500">*</span></label>
                            <input type="text" name="passport_number" value="{{ old('passport_number', $employee->passport_number) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nationality</label>
                            <select name="nationality" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                                <option value="">Select Country...</option>
                                <option value="Afghanistan" {{ old('nationality', $employee->nationality) === 'Afghanistan' ? 'selected' : '' }}>Afghanistan</option>
                                <option value="Bangladesh" {{ old('nationality', $employee->nationality) === 'Bangladesh' ? 'selected' : '' }}>Bangladesh</option>
                                <option value="India" {{ old('nationality', $employee->nationality) === 'India' ? 'selected' : '' }}>India</option>
                                <option value="Maldives" {{ old('nationality', $employee->nationality) === 'Maldives' ? 'selected' : '' }}>Maldives</option>
                                <option value="Nepal" {{ old('nationality', $employee->nationality) === 'Nepal' ? 'selected' : '' }}>Nepal</option>
                                <option value="Pakistan" {{ old('nationality', $employee->nationality) === 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                                <option value="Philippines" {{ old('nationality', $employee->nationality) === 'Philippines' ? 'selected' : '' }}>Philippines</option>
                                <option value="Sri Lanka" {{ old('nationality', $employee->nationality) === 'Sri Lanka' ? 'selected' : '' }}>Sri Lanka</option>
                                <option value="Other" {{ old('nationality', $employee->nationality) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">ID Number</label>
                            <input type="text" name="id_number" value="{{ old('id_number', $employee->id_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Number (Home Country)</label>
                            <input type="text" name="contact_number_home" value="{{ old('contact_number_home', $employee->contact_number_home) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secondary Phone</label>
                            <input type="text" name="secondary_phone" value="{{ old('secondary_phone', $employee->secondary_phone) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Address</label>
                            <textarea name="address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('address', $employee->address) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permanent Address</label>
                            <textarea name="permanent_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Emergency Contact --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) }}" placeholder="e.g., Father, Mother, Spouse, Sibling" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Compliance & Documents --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Compliance & Documents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Passport Number</label>
                            <input type="text" name="passport_number" value="{{ old('passport_number', $employee->passport_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Passport Expiry Date</label>
                            <input type="date" name="passport_expiry_date" value="{{ old('passport_expiry_date', $employee->passport_expiry_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Work Permit Number</label>
                            <input type="text" name="work_permit_number" value="{{ old('work_permit_number', $employee->work_permit_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Work Permit Fee Paid Until</label>
                            <input type="date" name="work_permit_fee_paid_until" value="{{ old('work_permit_fee_paid_until', $employee->work_permit_fee_paid_until?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Visa Number</label>
                            <input type="text" name="visa_number" value="{{ old('visa_number', $employee->visa_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Visa Expiry Date</label>
                            <input type="date" name="visa_expiry_date" value="{{ old('visa_expiry_date', $employee->visa_expiry_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quota Slot Number</label>
                            <input type="text" name="quota_slot_number" value="{{ old('quota_slot_number', $employee->quota_slot_number) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quota Slot Fee Paid Until</label>
                            <input type="date" name="quota_slot_fee_paid_until" value="{{ old('quota_slot_fee_paid_until', $employee->quota_slot_fee_paid_until?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Medical Checkup Expiry</label>
                            <input type="date" name="medical_checkup_expiry_date" value="{{ old('medical_checkup_expiry_date', $employee->medical_checkup_expiry_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Insurance Expiry</label>
                            <input type="date" name="insurance_expiry_date" value="{{ old('insurance_expiry_date', $employee->insurance_expiry_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
