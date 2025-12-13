<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserManagementController extends Controller
{
    /**
     * List all users
     */
    public function index()
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        return view('users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:admin,manager,mechanic,cashier,hr'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        return view('users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:admin,manager,mechanic,cashier,hr,customer'],
            'is_premium' => ['nullable', 'boolean'],
            'premium_features' => ['nullable', 'array'],
            'premium_features.*' => ['in:bill_upload,bill_sharing,expense_tracking,advanced_reports'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Handle premium subscription
        $user->is_premium = $request->has('is_premium');
        if ($user->is_premium) {
            // Set premium to expire in 30 days if newly enabled
            if (!$user->wasChanged('is_premium') || !$user->premium_expires_at) {
                $user->premium_expires_at = now()->addDays(30);
            }
            $user->premium_features = $request->input('premium_features', []);
        } else {
            $user->premium_expires_at = null;
            $user->premium_features = null;
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if (!Gate::allows('manage-users')) {
            abort(403, 'Unauthorized. You do not have permission to manage users.');
        }
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
