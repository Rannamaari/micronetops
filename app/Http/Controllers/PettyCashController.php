<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PettyCashController extends Controller
{
    /** List petty cash entries + balance */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // all / pending / approved / rejected
        $type   = $request->query('type', 'all');   // all / topup / expense

        $query = PettyCash::with(['user', 'approver'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $entries = $query->paginate(25)->withQueryString();

        $balance = PettyCash::currentBalance();

        // Get all approved entries for ledger history (ordered chronologically from oldest to newest)
        $ledgerEntries = PettyCash::with(['user', 'approver'])
            ->where('status', 'approved')
            ->orderBy('paid_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate running balance for each entry (starting from 0)
        $runningBalance = 0;
        $ledgerWithBalance = [];

        foreach ($ledgerEntries as $entry) {
            if ($entry->type === 'topup') {
                $runningBalance += $entry->amount;
            } else {
                $runningBalance -= $entry->amount;
            }
            
            $ledgerWithBalance[] = [
                'entry' => $entry,
                'balance_after' => $runningBalance,
            ];
        }

        // Reverse to show newest first (most recent at top)
        $ledgerWithBalance = array_reverse($ledgerWithBalance);

        return view('petty_cash.index', compact('entries', 'balance', 'status', 'type', 'ledgerWithBalance'));
    }

    /** Show form to create topup / expense (we'll use inline forms on index later if you prefer) */
    public function create()
    {
        return view('petty_cash.create');
    }

    /** Store a new petty cash record (topup or expense request) */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'     => ['required', 'in:topup,expense'],
            'amount'   => ['required', 'numeric', 'min:0.01'],
            'category' => ['nullable', 'string', 'max:100'],
            'purpose'  => ['required', 'string', 'max:255'],
        ]);

        $entry = PettyCash::create([
            'user_id'  => Auth::id(),
            'type'     => $validated['type'],
            'amount'   => $validated['amount'],
            'category' => $validated['category'] ?? null,
            'purpose'  => $validated['purpose'],
            'status'   => $validated['type'] === 'topup' ? 'approved' : 'pending',
            'approved_by' => $validated['type'] === 'topup' ? Auth::id() : null,
            'paid_at'  => $validated['type'] === 'topup' ? now() : null,
        ]);

        return redirect()
            ->route('petty-cash.index')
            ->with('success', 'Petty cash entry created.');
    }

    /** Approve an expense (admin/manager only) */
    public function approve(PettyCash $pettyCash)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. You do not have permission to approve petty cash.');
        }

        if ($pettyCash->status !== 'pending') {
            return back()->with('error', 'Only pending entries can be approved.');
        }

        // For expenses, you might want to check there is enough balance
        if ($pettyCash->type === 'expense' && PettyCash::currentBalance() < $pettyCash->amount) {
            return back()->with('error', 'Not enough petty cash balance to approve this expense.');
        }

        $pettyCash->status = 'approved';
        $pettyCash->approved_by = Auth::id();
        $pettyCash->paid_at = now();
        $pettyCash->save();

        return back()->with('success', 'Entry approved.');
    }

    /** Reject an expense request (admin/manager only) */
    public function reject(PettyCash $pettyCash)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. You do not have permission to reject petty cash.');
        }

        if ($pettyCash->status !== 'pending') {
            return back()->with('error', 'Only pending entries can be rejected.');
        }

        $pettyCash->status = 'rejected';
        $pettyCash->approved_by = Auth::id();
        $pettyCash->save();

        return back()->with('success', 'Entry rejected.');
    }
}
