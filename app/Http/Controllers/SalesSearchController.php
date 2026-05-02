<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailySalesLog;
use App\Models\Job;
use Illuminate\Http\Request;

class SalesSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        $customers = collect();
        $jobs = collect();
        $sales = collect();

        if ($query !== '') {
            $normalized = mb_strtolower($query);
            preg_match('/\d+/', $query, $matches);
            $numeric = $matches[0] ?? null;

            $customers = Customer::query()
                ->withCount('jobs')
                ->with('addresses')
                ->where(function ($q) use ($normalized, $numeric) {
                    if ($numeric !== null) {
                        $q->orWhere('id', (int) $numeric);
                    }

                    $q->orWhereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(address) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereHas('addresses', function ($addressQuery) use ($normalized) {
                            $addressQuery->whereRaw('LOWER(label) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(address) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(contact_name) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(contact_phone) LIKE ?', ['%' . $normalized . '%']);
                        });
                })
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            $jobs = Job::query()
                ->with('customer')
                ->where(function ($q) use ($normalized, $numeric) {
                    if ($numeric !== null) {
                        $q->orWhere('id', (int) $numeric);
                    }

                    $q->orWhereRaw('LOWER(title) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(customer_name) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(customer_phone) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(location) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(address) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(problem_description) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(customer_notes) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(search_note) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(po_number) LIKE ?', ['%' . $normalized . '%']);
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            $sales = DailySalesLog::query()
                ->with(['customer', 'job', 'lines'])
                ->where(function ($q) use ($normalized, $numeric) {
                    if ($numeric !== null) {
                        $q->orWhere('id', (int) $numeric)
                            ->orWhere('job_id', (int) $numeric);
                    }

                    $q->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(search_note) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereRaw('LOWER(po_number) LIKE ?', ['%' . $normalized . '%'])
                        ->orWhereHas('customer', function ($customerQuery) use ($normalized) {
                            $customerQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $normalized . '%']);
                        })
                        ->orWhereHas('job', function ($jobQuery) use ($normalized, $numeric) {
                            if ($numeric !== null) {
                                $jobQuery->orWhere('id', (int) $numeric);
                            }

                            $jobQuery->orWhereRaw('LOWER(customer_name) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(customer_phone) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(location) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(title) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(po_number) LIKE ?', ['%' . $normalized . '%']);
                        })
                        ->orWhereHas('lines', function ($lineQuery) use ($normalized) {
                            $lineQuery->whereRaw('LOWER(description) LIKE ?', ['%' . $normalized . '%'])
                                ->orWhereRaw('LOWER(note) LIKE ?', ['%' . $normalized . '%']);
                        });
                })
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        }

        return view('sales.search', [
            'query' => $query,
            'customers' => $customers,
            'jobs' => $jobs,
            'sales' => $sales,
        ]);
    }
}
