<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Deletion — {{ config('app.name', 'MicroNET Operations') }}</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <div class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold text-lg hover:underline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ config('app.name', 'MicroNET Operations') }}
            </a>
            <span class="text-sm text-gray-500 dark:text-gray-400">Data Deletion</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">

            <div class="bg-red-600 px-6 py-8 sm:px-10">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Data Deletion Request</h1>
                <p class="mt-1 text-red-200 text-sm">
                    How to request removal of your data from {{ config('app.name', 'MicroNET Operations') }}
                </p>
            </div>

            <div class="px-6 py-8 sm:px-10 space-y-8 text-sm leading-7 text-gray-700 dark:text-gray-300">

                <p>
                    We respect your right to control your personal data. If you have connected your Facebook account
                    or used our services and wish to have your data deleted, follow the steps below.
                </p>

                {{-- Facebook disconnection --}}
                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        Option 1 — Remove via Facebook Settings
                    </h2>
                    <p class="mb-3">You can revoke our app's access to your Facebook data directly from Facebook:</p>
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Go to <strong>Facebook → Settings &amp; Privacy → Settings</strong></li>
                        <li>Click <strong>Security and Login → Apps and Websites</strong></li>
                        <li>Find <strong>MicroMoto Marketing Manager</strong> and click <strong>Remove</strong></li>
                        <li>This will revoke all permissions and disconnect your Facebook data from our app</li>
                    </ol>
                </section>

                {{-- Email request --}}
                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        Option 2 — Email a Deletion Request
                    </h2>
                    <p class="mb-3">
                        Send us an email requesting full deletion of your data from our systems.
                        We will process your request within <strong>30 days</strong> and send a confirmation once complete.
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg px-5 py-4 space-y-1">
                        <p><span class="font-semibold">Email:</span>
                            <a href="mailto:hi@micronet.mv?subject=Data Deletion Request" class="text-indigo-600 dark:text-indigo-400 underline">
                                hi@micronet.mv
                            </a>
                        </p>
                        <p><span class="font-semibold">Subject:</span> Data Deletion Request</p>
                        <p><span class="font-semibold">Include:</span> Your name, email address, and Facebook Page name (if applicable)</p>
                    </div>
                </section>

                {{-- What gets deleted --}}
                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        What Data Will Be Deleted
                    </h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Facebook access tokens stored in our system</li>
                        <li>Facebook Page information linked to your account</li>
                        <li>WhatsApp Business account credentials</li>
                        <li>Leads retrieved from your Facebook Lead Ads</li>
                        <li>Any profile or account data associated with your Facebook login</li>
                    </ul>
                    <p class="mt-3 text-gray-500 dark:text-gray-400 text-xs">
                        Note: Business records (customer transactions, job records) that do not contain Facebook-sourced
                        personal data may be retained for legal and accounting compliance.
                    </p>
                </section>

                {{-- Timeline --}}
                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        Deletion Timeline
                    </h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">24h</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Request acknowledged</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">7 days</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Data removed from active systems</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">30 days</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fully purged from backups</div>
                        </div>
                    </div>
                </section>

            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 sm:px-10 flex flex-wrap gap-4 justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'MicroNET Operations') }}. All rights reserved.</span>
                <div class="flex gap-4">
                    <a href="{{ url('/') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Home</a>
                    <a href="{{ route('privacy.policy') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Terms</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
