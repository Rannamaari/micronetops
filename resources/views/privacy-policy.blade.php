<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — {{ config('app.name', 'MicroNET Operations') }}</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    {{-- Header bar --}}
    <div class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-semibold text-lg hover:underline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ config('app.name', 'MicroNET Operations') }}
            </a>
            <span class="text-sm text-gray-500 dark:text-gray-400">Privacy Policy</span>
        </div>
    </div>

    {{-- Main content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">

            {{-- Hero --}}
            <div class="bg-indigo-600 px-6 py-8 sm:px-10">
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Privacy Policy</h1>
                <p class="mt-1 text-indigo-200 text-sm">
                    Effective Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}
                    &nbsp;·&nbsp;
                    Last Updated: {{ \Carbon\Carbon::now()->format('F d, Y') }}
                </p>
            </div>

            <div class="px-6 py-8 sm:px-10 space-y-8 text-sm leading-7 text-gray-700 dark:text-gray-300">

                <p>
                    <strong>{{ config('app.name', 'MicroNET Operations') }}</strong> ("we", "us", or "our") operates this
                    application and associated services. This Privacy Policy explains how we collect, use, and protect
                    your information when you use our app, including our integration with Facebook and Meta platforms.
                </p>

                {{-- Section --}}
                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        1. Information We Collect
                    </h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Facebook Page information (Page name, ID, access tokens) to manage and post on your behalf</li>
                        <li>WhatsApp Business account information for customer communication</li>
                        <li>Lead information from Facebook Lead Ads</li>
                        <li>Basic profile information provided during login</li>
                        <li>Business data you enter into our system (customers, jobs, expenses)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        2. How We Use Your Information
                    </h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>To post content to your Facebook Pages on your behalf</li>
                        <li>To manage WhatsApp Business messaging with your customers</li>
                        <li>To retrieve and manage leads from your Facebook Lead Ads campaigns</li>
                        <li>To provide and improve our business operations platform</li>
                        <li>To communicate with you about your account and our services</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        3. Facebook and Meta Permissions
                    </h2>
                    <p class="mb-3">Our app requests the following Facebook permissions solely for the purposes stated above:</p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border border-gray-200 dark:border-gray-700 rounded">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Permission</th>
                                    <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Purpose</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach([
                                    ['catalog_management',           'Manage our product catalog on Meta'],
                                    ['pages_show_list',              'Display the list of Pages you manage'],
                                    ['ads_management',               'Manage ad campaigns on your Pages'],
                                    ['ads_read',                     'Read ad campaign performance data'],
                                    ['business_management',          'Manage Meta Business assets and settings'],
                                    ['pages_messaging',              'Send and receive messages via your Pages'],
                                    ['leads_retrieval',              'Access and manage leads from Lead Ads'],
                                    ['whatsapp_business_management', 'Manage your WhatsApp Business account'],
                                    ['pages_read_engagement',        'Read Page engagement and insights'],
                                    ['pages_manage_metadata',        'Manage Page settings and metadata'],
                                    ['pages_manage_ads',             'Create and manage ads on your Pages'],
                                    ['whatsapp_business_messaging',  'Send and receive WhatsApp messages'],
                                    ['pages_manage_posts',           'Create and publish posts on your Pages'],
                                ] as [$perm, $purpose])
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-2 font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">{{ $perm }}</td>
                                    <td class="px-4 py-2">{{ $purpose }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3">We will never use these permissions for purposes other than what is described in this policy.</p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        4. Data Sharing
                    </h2>
                    <p class="mb-2">We do not sell, trade, or share your personal data with third parties, except:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>When required by law or legal process</li>
                        <li>With service providers who assist in operating our platform (under strict confidentiality)</li>
                        <li>With Meta/Facebook as required by their platform integrations</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        5. Data Storage and Security
                    </h2>
                    <p>
                        Your data is stored securely on our servers. We use industry-standard encryption and security
                        practices to protect your information from unauthorized access, disclosure, or loss.
                        Access tokens obtained from Facebook are stored securely and are only used to perform
                        actions you have explicitly authorized.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        6. Data Retention
                    </h2>
                    <p>
                        We retain your data for as long as your account is active or as needed to provide our services.
                        You may request deletion of your data at any time by contacting us.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        7. Your Rights
                    </h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Access the personal data we hold about you</li>
                        <li>Request correction of inaccurate data</li>
                        <li>Request deletion of your data — see our <a href="{{ route('data-deletion') }}" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">Data Deletion page</a></li>
                        <li>Revoke Facebook permissions at any time via your Facebook settings</li>
                        <li>Withdraw consent for data processing at any time</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        8. Cookies
                    </h2>
                    <p>
                        Our app uses cookies only for session management and security purposes.
                        We do not use tracking cookies for advertising.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        9. Children's Privacy
                    </h2>
                    <p>
                        Our services are not directed to individuals under the age of 13.
                        We do not knowingly collect personal information from children.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        10. Changes to This Policy
                    </h2>
                    <p>
                        We may update this Privacy Policy from time to time. We will notify you of any significant
                        changes by updating the effective date at the top of this page.
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                        11. Contact Us
                    </h2>
                    <p class="mb-2">If you have any questions about this Privacy Policy, please contact us:</p>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg px-5 py-4 space-y-1">
                        <p><span class="font-semibold">Company:</span> {{ config('app.name', 'MicroNET Operations') }}</p>
                        <p><span class="font-semibold">Email:</span> hi@micronet.mv</p>
                        <p><span class="font-semibold">Website:</span> <a href="https://micronet.mv" class="text-indigo-600 dark:text-indigo-400 underline">micronet.mv</a></p>
                    </div>
                </section>

            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 sm:px-10 flex flex-wrap gap-4 justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'MicroNET Operations') }}. All rights reserved.</span>
                <div class="flex gap-4">
                    <a href="{{ url('/') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Home</a>
                    <a href="{{ route('data-deletion') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Data Deletion</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
