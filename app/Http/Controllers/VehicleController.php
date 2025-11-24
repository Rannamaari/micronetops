<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Store a vehicle for a given customer.
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'brand'               => ['nullable', 'string', 'max:100'],
            'model'               => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'year'                => ['nullable', 'integer'],
            'mileage'             => ['nullable', 'integer'],
        ]);

        $validated['customer_id'] = $customer->id;

        Vehicle::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Vehicle added successfully.');
    }
}
