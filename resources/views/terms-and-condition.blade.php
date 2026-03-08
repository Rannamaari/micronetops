<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms and Conditions — {{ config('app.name', 'MicroNET Operations') }}</title>

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
            <span class="text-sm text-gray-500 dark:text-gray-400">Terms and Conditions</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">

            <div class="bg-indigo-600 px-6 py-8 sm:px-10">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Terms and Conditions</h1>
                <p class="mt-1 text-indigo-200 text-sm">
                    Effective Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}
                    &nbsp;·&nbsp;
                    Last Updated: {{ \Carbon\Carbon::now()->format('F d, Y') }}
                </p>
            </div>

            <div class="px-6 py-8 sm:px-10 space-y-8 text-sm leading-7 text-gray-700 dark:text-gray-300">

                <p>
                    By accessing or using <strong>{{ config('app.name', 'MicroNET Operations') }}</strong> ("the App"),
                    you agree to be bound by these Terms and Conditions. If you do not agree, please do not use the App.
                </p>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        1. Use of the App
                    </h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>You must be at least 18 years old to use this App.</li>
                        <li>You agree to use the App only for lawful business purposes.</li>
                        <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                        <li>You must not use the App to spam, harass, or send unsolicited messages.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        2. Facebook and Meta Integration
                    </h2>
                    <p>
                        Our App integrates with Facebook and Meta platforms. By connecting your Facebook account or Pages,
                        you authorize us to act on your behalf as permitted by the permissions you grant.
                        You remain responsible for all content posted through the App to your Facebook Pages.
                        You must comply with <a href="https://www.facebook.com/terms" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline">Facebook's Terms of Service</a>
                        and <a href="https://developers.facebook.com/policy" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline">Meta's Platform Policies</a>.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        3. WhatsApp Business Messaging
                    </h2>
                    <p>
                        Use of WhatsApp Business messaging features must comply with
                        <a href="https://www.whatsapp.com/legal/business-policy" target="_blank" class="text-indigo-600 dark:text-indigo-400 underline">WhatsApp's Business Policy</a>.
                        You must not send spam, bulk unsolicited messages, or content that violates applicable laws.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        4. Intellectual Property
                    </h2>
                    <p>
                        All content, logos, and software associated with this App are the property of
                        {{ config('app.name', 'MicroNET Operations') }} and are protected by applicable intellectual
                        property laws. You may not copy, modify, or redistribute any part of the App without
                        our written permission.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        5. Limitation of Liability
                    </h2>
                    <p>
                        We are not liable for any direct, indirect, incidental, or consequential damages arising
                        from your use of the App or any third-party services (including Facebook or WhatsApp).
                        The App is provided "as is" without warranties of any kind.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        6. Termination
                    </h2>
                    <p>
                        We reserve the right to suspend or terminate your access to the App at any time
                        if you violate these Terms or engage in conduct we determine to be harmful.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        7. Changes to Terms
                    </h2>
                    <p>
                        We may update these Terms at any time. Continued use of the App after changes are posted
                        constitutes acceptance of the updated Terms.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        8. Contact
                    </h2>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg px-5 py-4 space-y-1">
                        <p><span class="font-semibold">Company:</span> {{ config('app.name', 'MicroNET Operations') }}</p>
                        <p><span class="font-semibold">Email:</span> hi@micronet.mv</p>
                        <p><span class="font-semibold">Website:</span> <a href="https://micronet.mv" class="text-indigo-600 dark:text-indigo-400 underline">micronet.mv</a></p>
                    </div>
                </section>

            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 sm:px-10 flex flex-wrap gap-4 justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'MicroNET Operations') }}. All rights reserved.</span>
                <div class="flex gap-4">
                    <a href="{{ url('/') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Home</a>
                    <a href="{{ route('privacy.policy') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                    <a href="{{ route('data-deletion') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Data Deletion</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
