<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    private const PHONE_RULES = ['required', 'string', 'max:50', 'regex:/^[0-9\\s,;\\/|()+-]+$/'];

    /**
     * List customers with search.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $phoneFilter = (string) $request->get('phone_filter', 'all');

        $query = Customer::with('vehicles');

        if ($search) {
            $s = mb_strtolower($search);
            $query->where(function($q) use ($s) {
                $q->whereRaw('lower(name) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(phone) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(email) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(address) like ?', ["%{$s}%"]);
            });
        }

        if ($phoneFilter === 'invalid') {
            $query->whereRaw("phone ~ '[A-Za-z]'");
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get the most recently created customer for the notice
        $lastCustomer = Customer::orderBy('created_at', 'desc')->first();

        return view('customers.index', compact('customers', 'search', 'lastCustomer', 'phoneFilter'));
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
            'phone'    => array_merge(self::PHONE_RULES, ['unique:customers,phone']),
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string', 'max:500'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'notes'    => ['nullable', 'string'],
        ], [
            'phone.regex' => 'Phone number can contain digits and common separators only. Letters are not allowed.',
        ]);

        $customer = Customer::create($validated);
        ActivityLog::record('customer.created', "Customer '{$customer->name}' ({$customer->phone}) created", $customer);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show a single customer with vehicles & AC units.
     */
    public function show(Customer $customer)
    {
        $customer->load(['vehicles', 'acUnits', 'jobs', 'addresses']);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer)
    {
        if (!Auth::user()->canEditCustomers()) {
            abort(403);
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
        if (!Auth::user()->canEditCustomers()) {
            abort(403);
        }
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => array_merge(self::PHONE_RULES, ['unique:customers,phone,' . $customer->id]),
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string', 'max:500'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'notes'    => ['nullable', 'string'],
        ], [
            'phone.regex' => 'Phone number can contain digits and common separators only. Letters are not allowed.',
        ]);

        $customer->update($validated);
        ActivityLog::record('customer.updated', "Customer '{$customer->name}' ({$customer->phone}) updated", $customer);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete a customer.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has any jobs
        if ($customer->jobs()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with existing jobs.');
        }

        // Delete associated vehicles and AC units
        $name = $customer->name;
        $phone = $customer->phone;
        $customer->vehicles()->delete();
        $customer->acUnits()->delete();
        $customer->delete();

        ActivityLog::record('customer.deleted', "Customer '{$name}' ({$phone}) deleted");

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
