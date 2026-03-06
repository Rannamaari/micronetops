<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    /**
     * GET /api/customers/search?q=keyword
     * Search customers by name or phone.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            return response()->json(['error' => 'Query parameter "q" is required.'], 422);
        }

        $s = mb_strtolower($q);

        $customers = Customer::where(function ($query) use ($s) {
            $query->whereRaw('lower(name) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(phone) like ?', ["%{$s}%"]);
        })
        ->orderBy('name')
        ->get(['id', 'name', 'phone', 'email', 'category']);

        return response()->json([
            'query' => $q,
            'data'  => $customers,
            'total' => $customers->count(),
        ]);
    }

    /**
     * POST /api/customers
     * Find existing customer by phone or create a new one.
     *
     * Body: { "name": "Ahmed Ali", "phone": "7001234", "category": "moto" }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'phone'    => ['required', 'string', 'max:50'],
                'email'    => ['nullable', 'email', 'max:255'],
                'category' => ['nullable', 'in:moto,ac'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $existing = Customer::where('phone', $validated['phone'])->first();

        if ($existing) {
            return response()->json([
                'created'  => false,
                'message'  => 'Customer already exists with this phone number.',
                'data'     => $existing->only(['id', 'name', 'phone', 'email', 'category']),
            ]);
        }

        $customer = Customer::create([
            'name'     => $validated['name'],
            'phone'    => $validated['phone'],
            'email'    => $validated['email'] ?? null,
            'category' => $validated['category'] ?? 'moto',
        ]);

        return response()->json([
            'created' => true,
            'message' => 'Customer created successfully.',
            'data'    => $customer->only(['id', 'name', 'phone', 'email', 'category']),
        ], 201);
    }
}
