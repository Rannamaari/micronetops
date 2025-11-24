<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * List customers.
     */
    public function index()
    {
        $customers = Customer::with('vehicles')
            ->orderBy('name')
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a new customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:50'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:20'], // moto, ac, both
            'notes'    => ['nullable', 'string'],
        ]);

        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show a single customer with vehicles & AC units.
     */
    public function show(Customer $customer)
    {
        $customer->load(['vehicles', 'acUnits', 'jobs']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:50'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:20'],
            'notes'    => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Optional: we can allow deleting later.
     */
    public function destroy(Customer $customer)
    {
        // For now, you might not want to delete if jobs exist.
        // So we just prevent it, or you can implement soft deletes later.
        return back()->with('error', 'Deleting customers is disabled for now.');
    }
}
