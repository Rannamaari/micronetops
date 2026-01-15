<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\User;
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

        $currentUser = Auth::user();

        $query = PettyCash::with(['user', 'approver'])
            ->orderByDesc('created_at');

        // Non-admin users only see their own transactions
        if (!$currentUser->isAdmin()) {
            $query->where('assigned_to', $currentUser->id);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $entries = $query->paginate(25)->withQueryString();

        // Show user's individual balance instead of central balance
        $balance = PettyCash::userBalance($currentUser);

        // Get approved entries for ledger history (filtered by user if not admin)
        $ledgerQuery = PettyCash::with(['user', 'approver'])
            ->where('status', 'approved');

        // Non-admin users only see their own ledger
        if (!$currentUser->isAdmin()) {
            $ledgerQuery->where('assigned_to', $currentUser->id);
        }

        $ledgerEntries = $ledgerQuery->orderBy('paid_at', 'asc')
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

        // Limit to last 10 transactions for display on main page
        $ledgerWithBalanceLimited = array_slice($ledgerWithBalance, 0, 10);
        $hasMoreTransactions = count($ledgerWithBalance) > 10;

        return view('petty_cash.index', compact('entries', 'balance', 'status', 'type', 'ledgerWithBalanceLimited', 'hasMoreTransactions'));
    }

    /** Show full transaction history with pagination */
    public function history(Request $request)
    {
        $currentUser = Auth::user();
        $balance = PettyCash::userBalance($currentUser);
        $perPage = 50; // Show 50 transactions per page
        $currentPage = $request->get('page', 1);

        // Get approved entries for calculating running balances (filtered by user if not admin)
        $ledgerQuery = PettyCash::with(['user', 'approver'])
            ->where('status', 'approved');

        // Non-admin users only see their own history
        if (!$currentUser->isAdmin()) {
            $ledgerQuery->where('assigned_to', $currentUser->id);
        }

        $allLedgerEntries = $ledgerQuery->orderBy('paid_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate running balance for ALL entries (needed for accurate balances)
        $runningBalance = 0;
        $allLedgerWithBalance = [];

        foreach ($allLedgerEntries as $entry) {
            if ($entry->type === 'topup') {
                $runningBalance += $entry->amount;
            } else {
                $runningBalance -= $entry->amount;
            }

            $allLedgerWithBalance[] = [
                'entry' => $entry,
                'balance_after' => $runningBalance,
            ];
        }

        // Reverse to show newest first
        $allLedgerWithBalance = array_reverse($allLedgerWithBalance);

        // Create manual pagination
        $total = count($allLedgerWithBalance);
        $lastPage = (int) ceil($total / $perPage);
        $currentPage = max(1, min($currentPage, $lastPage));
        $offset = ($currentPage - 1) * $perPage;

        $ledgerWithBalance = array_slice($allLedgerWithBalance, $offset, $perPage);

        // Create pagination object
        $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
            $ledgerWithBalance,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('petty_cash.history', compact('balance', 'pagination'));
    }

    /** Admin dashboard to view all user balances */
    public function adminDashboard(Request $request)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. Only admin can access this page.');
        }

        $userBalances = PettyCash::allUserBalances();
        $totalAllocated = $userBalances->sum('balance');

        return view('petty_cash.admin-dashboard', compact('userBalances', 'totalAllocated'));
    }

    /** Show form to top up a specific user */
    public function showTopUpForm(User $user)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. Only admin can top up users.');
        }

        $currentBalance = PettyCash::userBalance($user);

        return view('petty_cash.top-up-user', compact('user', 'currentBalance'));
    }

    /** Process top-up for a specific user */
    public function topUpUser(Request $request, User $user)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. Only admin can top up users.');
        }

        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'purpose' => ['required', 'string', 'max:255'],
        ]);

        PettyCash::create([
            'user_id'     => Auth::id(),
            'assigned_to' => $user->id,
            'type'        => 'topup',
            'amount'      => $validated['amount'],
            'purpose'     => $validated['purpose'],
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'paid_at'     => now(),
        ]);

        return redirect()
            ->route('petty-cash.admin-dashboard')
            ->with('success', "Successfully topped up {$user->name} with " . number_format($validated['amount'], 2) . " MVR.");
    }

    /** View transaction history for a specific user */
    public function userHistory(User $user)
    {
        if (!Gate::allows('approve-petty-cash')) {
            abort(403, 'Unauthorized. Only admin can view user history.');
        }

        $transactions = PettyCash::with(['user', 'approver'])
            ->where('assigned_to', $user->id)
            ->where('status', 'approved')
            ->orderBy('paid_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $balance = PettyCash::userBalance($user);

        return view('petty_cash.user-history', compact('user', 'transactions', 'balance'));
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
            'user_id'     => Auth::id(),
            'assigned_to' => Auth::id(), // Assign to current user
            'type'        => $validated['type'],
            'amount'      => $validated['amount'],
            'category'    => $validated['category'] ?? null,
            'purpose'     => $validated['purpose'],
            'status'      => $validated['type'] === 'topup' ? 'approved' : 'pending',
            'approved_by' => $validated['type'] === 'topup' ? Auth::id() : null,
            'paid_at'     => $validated['type'] === 'topup' ? now() : null,
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

        // For expenses, check if the user has enough balance
        if ($pettyCash->type === 'expense' && $pettyCash->assigned_to) {
            $assignedUser = User::find($pettyCash->assigned_to);
            if ($assignedUser) {
                $userBalance = PettyCash::userBalance($assignedUser);
                if ($userBalance < $pettyCash->amount) {
                    return back()->with('error', "Not enough balance for {$assignedUser->name}. Current balance: " . number_format($userBalance, 2) . " MVR");
                }
            }
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
