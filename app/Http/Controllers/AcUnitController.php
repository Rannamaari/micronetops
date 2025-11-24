<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\AcUnit;
use Illuminate\Http\Request;

class AcUnitController extends Controller
{
    /**
     * Store an AC unit for a given customer.
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'brand'                => ['nullable', 'string', 'max:100'],
            'btu'                  => ['nullable', 'integer'],
            'gas_type'             => ['nullable', 'string', 'max:50'], // R32, R410
            'indoor_units'         => ['nullable', 'integer'],
            'outdoor_units'        => ['nullable', 'integer'],
            'location_description' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['customer_id'] = $customer->id;

        AcUnit::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'AC unit added successfully.');
    }
}
