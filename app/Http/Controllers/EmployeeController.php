<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Only admins can access employee management
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized. Only administrators can manage employees.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of employees
     */
    public function index(Request $request)
    {
        $query = Employee::query();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('employee_number', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('position', 'ilike', "%{$search}%");
            });
        }

        // Filter expiring documents
        if ($request->has('expiring') && $request->expiring === 'yes') {
            $query->expiringDocuments(30);
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'unique:employees,employee_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:employees,email'],
            'phone' => ['required', 'string'],
            'secondary_phone' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string'],
            'emergency_contact_phone' => ['nullable', 'string'],
            'type' => ['required', 'in:full-time,part-time,contract'],
            'position' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,terminated'],
            'address' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            // Compliance fields
            'work_permit_number' => ['nullable', 'string'],
            'date_of_arrival' => ['nullable', 'date'],
            'work_permit_fee_paid_until' => ['nullable', 'date'],
            'quota_slot_fee_paid_until' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string'],
            'passport_expiry_date' => ['nullable', 'date'],
            'visa_number' => ['nullable', 'string'],
            'visa_expiry_date' => ['nullable', 'date'],
            'quota_slot_number' => ['nullable', 'string'],
            'medical_checkup_expiry_date' => ['nullable', 'date'],
            'insurance_expiry_date' => ['nullable', 'date'],
            'insurance_number' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::create($validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Employee '{$employee->name}' has been created successfully.");
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee)
    {
        $employee->load(['salaries' => function($q) {
            $q->latest()->limit(6);
        }, 'loans', 'leaves' => function($q) {
            $q->latest()->limit(10);
        }, 'allowances']);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the employee
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'unique:employees,employee_number,' . $employee->id],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:employees,email,' . $employee->id],
            'phone' => ['required', 'string'],
            'secondary_phone' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string'],
            'emergency_contact_phone' => ['nullable', 'string'],
            'type' => ['required', 'in:full-time,part-time,contract'],
            'position' => ['required', 'string'],
            'department' => ['nullable', 'string'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,terminated'],
            'address' => ['nullable', 'string'],
            'nationality' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'id_number' => ['nullable', 'string'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            // Compliance fields
            'work_permit_number' => ['nullable', 'string'],
            'date_of_arrival' => ['nullable', 'date'],
            'work_permit_fee_paid_until' => ['nullable', 'date'],
            'quota_slot_fee_paid_until' => ['nullable', 'date'],
            'passport_number' => ['nullable', 'string'],
            'passport_expiry_date' => ['nullable', 'date'],
            'visa_number' => ['nullable', 'string'],
            'visa_expiry_date' => ['nullable', 'date'],
            'quota_slot_number' => ['nullable', 'string'],
            'medical_checkup_expiry_date' => ['nullable', 'date'],
            'insurance_expiry_date' => ['nullable', 'date'],
            'insurance_number' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee->update($validated);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Employee '{$employee->name}' has been updated successfully.");
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        $name = $employee->name;
        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', "Employee '{$name}' has been deleted successfully.");
    }
}
