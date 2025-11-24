<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * List all roles
     */
    public function index()
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        $roles = Role::withCount('users')->orderBy('name')->paginate(20);
        return view('roles.index', compact('roles'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        return view('roles.create');
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'in:0,1'],
        ]);

        $validated['is_active'] = (bool) ($request->input('is_active', 1));

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Role::create($validated);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Role $role)
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        return view('roles.edit', compact('role'));
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:roles,slug,' . $role->id],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'in:0,1'],
        ]);

        $validated['is_active'] = (bool) ($request->input('is_active', 1));

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $role->update($validated);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        if (!Gate::allows('manage-roles')) {
            abort(403, 'Unauthorized. You do not have permission to manage roles.');
        }
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role that is assigned to users.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
