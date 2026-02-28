<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Vendor::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%");
            });
        }

        $vendors = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('vendors.index', compact('vendors', 'search'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        $existing = Vendor::where('phone', $validated['phone'])->orderBy('id')->first();
        if ($existing) {
            $existing->update([
                'name' => $existing->name ?: $validated['name'],
                'contact_name' => $existing->contact_name ?: $validated['contact_name'],
                'address' => $existing->address ?: $validated['address'],
                'is_active' => $existing->is_active || $validated['is_active'],
            ]);

            return redirect()->route('vendors.edit', $existing)
                ->with('success', 'Vendor already exists. Updated missing details.');
        }

        Vendor::create($validated);

        if ($request->expectsJson()) {
            $vendor = $existing ?? Vendor::where('phone', $validated['phone'])->orderBy('id')->first();
            return response()->json([
                'id' => $vendor?->id,
                'name' => $vendor?->name ?? $validated['name'],
                'phone' => $vendor?->phone ?? $validated['phone'],
                'contact_name' => $vendor?->contact_name ?? $validated['contact_name'] ?? null,
                'address' => $vendor?->address ?? $validated['address'] ?? null,
            ]);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }
}
