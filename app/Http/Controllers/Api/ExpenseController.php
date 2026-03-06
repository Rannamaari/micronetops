<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PettyCash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    /**
     * POST /api/expenses
     * Record a petty cash expense (submitted as pending, approved by admin on the web).
     *
     * Body: { "amount": 150, "purpose": "Bought cleaning supplies", "category": "supplies" }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount'   => ['required', 'numeric', 'min:0.01'],
                'purpose'  => ['required', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:100'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        // Assign expense to first admin user (the bot acts on behalf of the business)
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            return response()->json(['error' => 'No admin user found on the system.'], 500);
        }

        $entry = PettyCash::create([
            'user_id'     => $admin->id,
            'assigned_to' => $admin->id,
            'type'        => 'expense',
            'amount'      => $validated['amount'],
            'purpose'     => $validated['purpose'],
            'category'    => $validated['category'] ?? null,
            'status'      => 'pending',
        ]);

        return response()->json([
            'message'  => 'Expense recorded. Pending admin approval.',
            'data'     => [
                'id'       => $entry->id,
                'amount'   => number_format((float) $entry->amount, 2),
                'purpose'  => $entry->purpose,
                'category' => $entry->category,
                'status'   => $entry->status,
            ],
        ], 201);
    }

    /**
     * GET /api/expenses/pending
     * List all pending petty cash expenses (awaiting admin approval).
     */
    public function pending(): JsonResponse
    {
        $entries = PettyCash::with('user')
            ->where('type', 'expense')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get(['id', 'user_id', 'amount', 'purpose', 'category', 'status', 'created_at']);

        return response()->json([
            'total' => $entries->count(),
            'data'  => $entries->map(fn($e) => [
                'id'         => $e->id,
                'amount'     => number_format((float) $e->amount, 2),
                'purpose'    => $e->purpose,
                'category'   => $e->category,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }
}
