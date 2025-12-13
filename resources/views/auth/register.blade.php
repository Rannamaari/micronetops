<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name', 'MicroNET') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen py-6">
        <!-- Logo -->
        <div class="text-center mb-6">
            <a href="/">
                <x-application-logo class="w-20 h-20 inline-block" />
            </a>
        </div>

        <!-- Info Message (when redirected from Rattehin) -->
        @if(session('info'))
            <div class="max-w-7xl mx-auto px-4 mb-4">
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Container -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Offer Banner (Full Width) -->
            <div class="mb-6 bg-white border-2 border-blue-200 rounded-lg p-5 shadow-sm">
                <div class="flex items-start">
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

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- LEFT COLUMN: Registration Form -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Your Account</h2>

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

                        <div class="flex items-center justify-between mt-6">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Register & Claim Offer') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT COLUMN: Rattehin Features -->
                <div class="space-y-6">
                    <!-- Rattehin App Features -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">✨ Discover Rattehin</h3>
                            <p class="text-lg font-semibold text-blue-600 mb-3">Split smarter. Settle faster. Stay fair.</p>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                Rattehin is a simple and smart bill-splitting app designed to take the stress out of shared expenses.
                                Whether you're dining out, shopping together, or managing group costs, Rattehin helps you split bills fairly and clearly.
                            </p>
                        </div>

                        <!-- Premium Features -->
                        <div class="bg-white rounded-lg p-4 mb-3">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-gray-900 flex items-center">
                                    <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded mr-2">PREMIUM</span>
                                    Just 15 RF/month
                                </h4>
                            </div>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>Upload & Scan Bills:</strong> Take photos of receipts and auto-extract items with OCR</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>Share Bills:</strong> Beautiful formatted images shared via WhatsApp, Viber, or Facebook</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Coming Soon Features -->
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
                            <h4 class="font-bold text-purple-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Coming Soon - Premium Features
                            </h4>
                            <ul class="space-y-2 text-sm text-purple-900">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>Expense Tracker:</strong> Track petrol, groceries, and all your daily expenses in one place</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>Reminder & Todo Tools:</strong> Never forget important tasks with smart reminders</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-purple-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                    </svg>
                                    <span><strong>Job Clustering:</strong> Organize and manage multiple jobs efficiently</span>
                                </li>
                            </ul>
                            <p class="mt-3 text-xs text-purple-700 font-semibold">
                                💡 Sign up today and be ready when these features launch! Premium subscribers get early access.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
