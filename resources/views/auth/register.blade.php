<x-guest-layout>
    <!-- Info Message (when redirected from Rattehin) -->
    @if(session('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Special Offer Banner -->
    <div class="mb-6 bg-white border-2 border-blue-200 rounded-lg p-5 shadow-sm">
        <div class="flex items-start mb-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Welcome Offer!</h3>
                <p class="text-sm text-gray-700 mb-2">
                    Sign up today and get <strong class="text-blue-600">10% OFF</strong> on any service at Micro Moto Garage
                </p>
                <p class="text-xs text-gray-600 mb-3">
                    ⏰ Redeem within 3 days from registration. Screenshot this page as proof!
                </p>
                <details class="text-xs text-gray-500">
                    <summary class="cursor-pointer hover:text-gray-700 font-medium">Terms & Conditions</summary>
                    <ul class="mt-2 ml-4 list-disc space-y-1">
                        <li>Offer valid for new registrations only</li>
                        <li>Must be redeemed within 3 days of registration</li>
                        <li>Screenshot of this page required as proof</li>
                        <li>Valid at MicroNET Micro Moto Garage only</li>
                        <li>10% discount applies to any single service</li>
                        <li>Cannot be combined with other offers or promotions</li>
                        <li>MicroNET reserves the right to modify or cancel this offer at any time</li>
                    </ul>
                </details>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ acceptedMarketing: false }">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="e.g., Ahmed Mohamed" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="ahmed@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="7771234 or 9771234" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">For order updates and offer notifications</p>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="Minimum 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password"
                            placeholder="Re-enter your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Marketing Consent -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="marketing_consent"
                           name="marketing_consent"
                           type="checkbox"
                           value="1"
                           x-model="acceptedMarketing"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                </div>
                <div class="ml-3 text-sm">
                    <label for="marketing_consent" class="font-medium text-gray-700 cursor-pointer">
                        Yes, I want to receive promotional offers from MicroNET
                    </label>
                    <p class="text-xs text-gray-600 mt-1">
                        Get exclusive deals on motorcycle servicing, AC maintenance, and special discounts. You can unsubscribe anytime.
                    </p>
                </div>
            </div>
        </div>

        <!-- Discount Reminder -->
        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Remember:</strong> Screenshot this page after registration to redeem your 10% discount! Valid for 3 days.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register & Claim Offer') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
