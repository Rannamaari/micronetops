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
                'category' => ['nullable', 'in:moto,ac,it'],
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

    /**
     * GET /api/customers/{id}
     * Get a single customer with their job history summary.
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::with('jobs:id,customer_id,job_date,total_amount,status,job_type')->find($id);

        if (!$customer) {
            return response()->json(['error' => "Customer #{$id} not found."], 404);
        }

        return response()->json([
            'data' => [
                'id'         => $customer->id,
                'name'       => $customer->name,
                'phone'      => $customer->phone,
                'email'      => $customer->email,
                'address'    => $customer->address,
                'category'   => $customer->category,
                'notes'      => $customer->notes,
                'total_jobs' => $customer->jobs->count(),
                'total_spent'=> number_format($customer->jobs->sum('total_amount'), 2),
                'created_at' => $customer->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * PATCH /api/customers/{id}
     * Update a customer's details. Only send fields you want to change.
     *
     * Body: { "name": "New Name", "phone": "7009999", "email": "x@y.com", "address": "...", "notes": "..." }
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => "Customer #{$id} not found."], 404);
        }

        try {
            $validated = $request->validate([
                'name'     => ['sometimes', 'string', 'max:255'],
                'phone'    => ['sometimes', 'string', 'max:50', 'unique:customers,phone,' . $id],
                'email'    => ['sometimes', 'nullable', 'email', 'max:255'],
                'address'  => ['sometimes', 'nullable', 'string', 'max:500'],
                'notes'    => ['sometimes', 'nullable', 'string'],
                'category' => ['sometimes', 'in:moto,ac,it'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully.',
            'data'    => $customer->fresh()->only(['id', 'name', 'phone', 'email', 'address', 'category', 'notes']),
        ]);
    }

    /**
     * DELETE /api/customers/{id}
     * Delete a customer. Blocked if they have existing jobs.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => "Customer #{$id} not found."], 404);
        }

        if ($customer->jobs()->count() > 0) {
            return response()->json([
                'error' => "Cannot delete \"{$customer->name}\" — they have existing job records. Remove those first.",
            ], 422);
        }

        $name = $customer->name;
        $customer->vehicles()->delete();
        $customer->acUnits()->delete();
        $customer->delete();

        return response()->json(['message' => "Customer \"{$name}\" deleted successfully."]);
    }
}
