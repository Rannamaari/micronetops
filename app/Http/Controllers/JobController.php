<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\AcUnit;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * List recent jobs.
     */
    public function index(Request $request)
    {
        // status filter from query string, default = pending
        $status = $request->query('status', 'pending'); // pending | in_progress | completed | all
        $dateFilter = $request->query('date', null); // today | yesterday | previous_month | current_month

        $query = Job::with(['customer', 'vehicle', 'acUnit', 'assignedUser'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Apply date filter
        if ($dateFilter) {
            $now = now();
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'yesterday':
                    $yesterday = $now->copy()->subDay();
                    $query->whereDate('created_at', $yesterday->toDateString());
                    break;
                case 'previous_month':
                    $firstDayPreviousMonth = $now->copy()->subMonth()->startOfMonth();
                    $firstDayCurrentMonth = $now->copy()->startOfMonth();
                    $query->where('created_at', '>=', $firstDayPreviousMonth->toDateTimeString())
                        ->where('created_at', '<', $firstDayCurrentMonth->toDateTimeString());
                    break;
                case 'current_month':
                    $firstDayCurrentMonth = $now->copy()->startOfMonth();
                    $query->where('created_at', '>=', $firstDayCurrentMonth->toDateTimeString());
                    break;
            }
        }

        $jobs = $query->paginate(20)->withQueryString();

        // (Optional) counts for tabs – nice UX but not mandatory
        $statusCounts = [
            'pending' => Job::where('status', 'pending')->count(),
            'in_progress' => Job::where('status', 'in_progress')->count(),
            'completed' => Job::where('status', 'completed')->count(),
            'all' => Job::count(),
        ];

        return view('jobs.index', compact('jobs', 'status', 'statusCounts', 'dateFilter'));
    }

    /**
     * Show the create job form.
     */
    public function create()
    {
        // Load customers with their bikes & AC units
        $customers = Customer::with([
            'vehicles:id,customer_id,brand,model,registration_number,year,mileage',
            'acUnits:id,customer_id,brand,btu,gas_type,location_description',
        ])->orderBy('name')->get();

        return view('jobs.create', compact('customers'));
    }

    /**
     * Store a new job.
     */
    public function store(Request $request)
    {
        $baseRules = [
            'job_type' => ['required', Rule::in(['moto', 'ac'])],
            'job_category' => ['required', 'string', 'max:50'],
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'ac_unit_id' => ['nullable', 'exists:ac_units,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['nullable', 'string'],
        ];

        $validated = $request->validate($baseRules);

        // Optional stricter rules (you can relax later if needed)
        if ($validated['job_type'] === 'moto') {
            // For pickup jobs, pickup_location is important
            if ($validated['job_category'] === 'pickup' && empty($validated['pickup_location'])) {
                return back()
                    ->withErrors(['pickup_location' => 'Pickup location is required for pickup jobs.'])
                    ->withInput();
            }
        }

        if ($validated['job_type'] === 'ac') {
            if (empty($validated['address'])) {
                return back()
                    ->withErrors(['address' => 'Address is recommended for AC jobs.'])
                    ->withInput();
            }
        }

        $job = Job::create([
            'job_type' => $validated['job_type'],
            'job_category' => $validated['job_category'],
            'customer_id' => $validated['customer_id'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'ac_unit_id' => $validated['ac_unit_id'] ?? null,
            'address' => $validated['address'] ?? null,
            'pickup_location' => $validated['pickup_location'] ?? null,
            'problem_description' => $validated['problem_description'] ?? null,
            'status' => 'pending',
            'labour_total' => 0,
            'parts_total' => 0,
            'travel_charges' => 0,
            'discount' => 0,
            'total_amount' => 0,
        ]);

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', 'Job created successfully.');
    }

    /**
     * Show a single job (basic for now).
     */
    public function show(Job $job)
    {
        $job->load(['customer', 'vehicle', 'acUnit', 'assignedUser', 'items.inventoryItem', 'payments']);

        $inventoryItems = InventoryItem::where('is_active', true)
            ->orderBy('name')
            ->get();

        $serviceItems = $inventoryItems->where('is_service', true);
        $partItems = $inventoryItems->where('is_service', false);

        return view('jobs.show', compact('job', 'inventoryItems', 'serviceItems', 'partItems'));
    }

    /**
     * Update job charges.
     */
    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'travel_charges' => ['required', 'numeric', 'min:0'],
            'discount' => ['required', 'numeric', 'min:0'],
        ]);

        $job->travel_charges = $validated['travel_charges'];
        $job->discount = $validated['discount'];
        $job->save();

        // Labour + parts will be recalculated from items
        $job->recalculateTotals();

        return back()->with('success', 'Charges updated successfully.');
    }

    /**
     * Show invoice for a job.
     */
    public function invoice(Job $job)
    {
        $job->load(['customer', 'vehicle', 'acUnit', 'items.inventoryItem', 'payments']);

        // Brand selection based on job type
        if ($job->job_type === 'ac') {
            $brand = [
                'name' => 'Micro Cool',
                'tagline' => 'We Fix, You Chill',
                'address' => 'Janavaree Hingun, Malé, Maldives',
                'phone' => '+960 9996210',
                'website' => 'cool.micronet.mv',
            ];
        } else {
            $brand = [
                'name' => 'Micro Moto Garage',
                'tagline' => 'Affordable & Reliable Motorbike Care',
                'address' => 'Janavaree Hingun, Malé, Maldives',
                'phone' => '+960 9996210',
                'website' => 'garage.micronet.mv',
            ];
        }

        // Simple invoice number pattern: JOB-<id>
        $invoiceNumber = 'JOB-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);

        return view('jobs.invoice', [
            'job' => $job,
            'brand' => $brand,
            'invoiceNumber' => $invoiceNumber,
        ]);
    }

    /**
     * Delete a job.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job deleted successfully.');
    }
}
