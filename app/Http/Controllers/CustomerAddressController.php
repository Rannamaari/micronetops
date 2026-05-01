<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    private const PHONE_RULES = ['nullable', 'string', 'max:50', 'regex:/^[0-9\\s,;\\/|()+-]+$/'];

    public function store(Request $request, Customer $customer)
    {
        $this->authorizeEditCustomers();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => self::PHONE_RULES,
            'is_default' => ['nullable', 'boolean'],
        ], [
            'contact_phone.regex' => 'Contact phone can contain digits and common separators only. Letters are not allowed.',
        ]);

        $address = $customer->addresses()->create([
            'label' => $validated['label'],
            'address' => $validated['address'],
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        $this->syncCustomerAddressDefaults($customer, $address);

        return back()->with('success', 'Customer address added successfully.');
    }

    public function update(Request $request, Customer $customer, CustomerAddress $address)
    {
        $this->authorizeEditCustomers();
        $this->ensureAddressBelongsToCustomer($customer, $address);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => self::PHONE_RULES,
            'is_default' => ['nullable', 'boolean'],
        ], [
            'contact_phone.regex' => 'Contact phone can contain digits and common separators only. Letters are not allowed.',
        ]);

        $address->update([
            'label' => $validated['label'],
            'address' => $validated['address'],
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        $this->syncCustomerAddressDefaults($customer, $address);

        return back()->with('success', 'Customer address updated successfully.');
    }

    public function destroy(Customer $customer, CustomerAddress $address)
    {
        $this->authorizeEditCustomers();
        $this->ensureAddressBelongsToCustomer($customer, $address);

        $wasDefault = $address->is_default;
        $address->delete();

        $nextDefault = $customer->addresses()->orderByDesc('is_default')->orderBy('id')->first();
        if ($nextDefault) {
            if (!$nextDefault->is_default) {
                $nextDefault->is_default = true;
                $nextDefault->save();
            }
            $customer->update(['address' => $nextDefault->address]);
        } elseif ($wasDefault) {
            $customer->update(['address' => null]);
        }

        return back()->with('success', 'Customer address deleted successfully.');
    }

    private function syncCustomerAddressDefaults(Customer $customer, CustomerAddress $address): void
    {
        $shouldBeDefault = $address->is_default || !$customer->addresses()->where('id', '!=', $address->id)->where('is_default', true)->exists();

        if ($shouldBeDefault) {
            $customer->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);

            if (!$address->is_default) {
                $address->is_default = true;
                $address->save();
            }

            $customer->update(['address' => $address->address]);
            return;
        }

        $defaultAddress = $customer->addresses()->where('is_default', true)->first();
        if ($defaultAddress) {
            $customer->update(['address' => $defaultAddress->address]);
        }
    }

    private function ensureAddressBelongsToCustomer(Customer $customer, CustomerAddress $address): void
    {
        abort_unless((int) $address->customer_id === (int) $customer->id, 404);
    }

    private function authorizeEditCustomers(): void
    {
        if (!auth()->user()?->canEditCustomers()) {
            abort(403);
        }
    }
}
