<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'marketing_consent' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Regular customers, not staff
            'marketing_consent' => $request->has('marketing_consent'),
            'offer_expires_at' => now()->addDays(3), // Free oil change valid for 3 days
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Show success message with offer details
        session()->flash('offer_claimed', true);
        session()->flash('offer_expires', $user->offer_expires_at->format('M d, Y'));

        // Redirect customers to Rattehin, staff to dashboard
        if ($user->isCustomer()) {
            return redirect(route('rattehin.index'));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
