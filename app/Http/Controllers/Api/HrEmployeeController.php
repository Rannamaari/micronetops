<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrEmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query()->orderBy('name');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }
        if ($company = $request->query('company')) {
            $query->where('company', $company);
        }
        if ($search = trim((string) $request->query('q', ''))) {
            $s = mb_strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('lower(name) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(employee_number) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(phone) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(position) like ?', ["%{$s}%"]);
            });
        }

        $employees = $query->limit(200)->get();

        return response()->json([
            'total' => $employees->count(),
            'data' => $employees->map(fn (Employee $e) => [
                'id' => $e->id,
                'employee_number' => $e->employee_number,
                'company' => $e->company,
                'name' => $e->name,
                'phone' => $e->phone,
                'email' => $e->email,
                'type' => $e->type,
                'position' => $e->position,
                'hire_date' => $e->hire_date?->format('Y-m-d'),
                'status' => $e->status,
                'basic_salary' => (float) $e->basic_salary,
                'basic_salary_usd' => $e->basic_salary_usd !== null ? (float) $e->basic_salary_usd : null,
            ]),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => "Employee #{$id} not found."], 404);
        }

        return response()->json([
            'data' => $employee,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_number' => ['required', 'string', 'unique:employees,employee_number'],
                'company' => ['required', Rule::in(['Micro Cool', 'Micro Moto Garage', 'Micronet', 'Micronet - Easy Fix'])],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'unique:employees,email'],
                'phone' => ['required', 'string', 'max:50'],
                'secondary_phone' => ['nullable', 'string', 'max:50'],
                'type' => ['required', Rule::in(['full-time', 'part-time', 'contract'])],
                'position' => ['required', 'string', 'max:255'],
                'department' => ['nullable', 'string', 'max:255'],
                'hire_date' => ['required', 'date'],
                'status' => ['required', Rule::in(['active', 'inactive', 'terminated'])],
                'address' => ['nullable', 'string'],
                'nationality' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['nullable', 'date'],
                'id_number' => ['nullable', 'string', 'max:255'],
                'basic_salary' => ['required', 'numeric', 'min:0'],
                'basic_salary_usd' => ['nullable', 'numeric', 'min:0'],
                'work_status' => ['required', Rule::in(['permanent', 'contract'])],

                // Compliance (keep aligned with web form: passport_number required)
                'passport_number' => ['required', 'string', 'max:255'],
                'passport_expiry_date' => ['nullable', 'date'],
                'work_permit_number' => ['nullable', 'string', 'max:255'],
                'work_permit_fee_paid_until' => ['nullable', 'date'],
                'visa_number' => ['nullable', 'string', 'max:255'],
                'visa_expiry_date' => ['nullable', 'date'],
                'quota_slot_number' => ['nullable', 'string', 'max:255'],
                'quota_slot_fee_paid_until' => ['nullable', 'date'],
                'medical_checkup_expiry_date' => ['nullable', 'date'],
                'insurance_number' => ['nullable', 'string', 'max:255'],
                'insurance_provider' => ['nullable', 'string', 'max:255'],
                'insurance_expiry_date' => ['nullable', 'date'],
                'notes' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        // If USD provided, auto-calc MVR (match web logic)
        if (!empty($validated['basic_salary_usd'])) {
            $validated['basic_salary'] = (float) $validated['basic_salary_usd'] * 15.42;
        }

        $employee = Employee::create($validated);
        ActivityLog::record('hr.employee_created', "API: Employee #{$employee->id} created ({$employee->name})", $employee, [], null, 'api');

        return response()->json([
            'message' => 'Employee created.',
            'data' => $employee,
        ], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => "Employee #{$id} not found."], 404);
        }

        try {
            $validated = $request->validate([
                'employee_number' => ['sometimes', 'string', 'unique:employees,employee_number,' . $employee->id],
                'company' => ['sometimes', Rule::in(['Micro Cool', 'Micro Moto Garage', 'Micronet', 'Micronet - Easy Fix'])],
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'nullable', 'email', 'unique:employees,email,' . $employee->id],
                'phone' => ['sometimes', 'string', 'max:50'],
                'secondary_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
                'type' => ['sometimes', Rule::in(['full-time', 'part-time', 'contract'])],
                'position' => ['sometimes', 'string', 'max:255'],
                'department' => ['sometimes', 'nullable', 'string', 'max:255'],
                'hire_date' => ['sometimes', 'date'],
                'status' => ['sometimes', Rule::in(['active', 'inactive', 'terminated'])],
                'address' => ['sometimes', 'nullable', 'string'],
                'nationality' => ['sometimes', 'nullable', 'string', 'max:255'],
                'date_of_birth' => ['sometimes', 'nullable', 'date'],
                'id_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'basic_salary' => ['sometimes', 'numeric', 'min:0'],
                'basic_salary_usd' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'work_status' => ['sometimes', Rule::in(['permanent', 'contract'])],

                'passport_number' => ['sometimes', 'string', 'max:255'],
                'passport_expiry_date' => ['sometimes', 'nullable', 'date'],
                'work_permit_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'work_permit_fee_paid_until' => ['sometimes', 'nullable', 'date'],
                'visa_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'visa_expiry_date' => ['sometimes', 'nullable', 'date'],
                'quota_slot_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'quota_slot_fee_paid_until' => ['sometimes', 'nullable', 'date'],
                'medical_checkup_expiry_date' => ['sometimes', 'nullable', 'date'],
                'insurance_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'insurance_provider' => ['sometimes', 'nullable', 'string', 'max:255'],
                'insurance_expiry_date' => ['sometimes', 'nullable', 'date'],
                'notes' => ['sometimes', 'nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        if (array_key_exists('basic_salary_usd', $validated) && !empty($validated['basic_salary_usd'])) {
            $validated['basic_salary'] = (float) $validated['basic_salary_usd'] * 15.42;
        }

        $employee->update($validated);
        ActivityLog::record('hr.employee_updated', "API: Employee #{$employee->id} updated", $employee, [], null, 'api');

        return response()->json([
            'message' => 'Employee updated.',
            'data' => $employee->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['error' => "Employee #{$id} not found."], 404);
        }

        $name = $employee->name;
        $employee->delete();
        ActivityLog::record('hr.employee_deleted', "API: Employee #{$id} deleted ({$name})", null, [], null, 'api');

        return response()->json(['message' => "Employee \"{$name}\" deleted."]);
    }
}

