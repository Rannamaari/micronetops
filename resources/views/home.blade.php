<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Micronet - IT, Security, Garage & AC Solutions in Maldives | Rattehin Bill Splitter</title>
    <meta name="description" content="Micronet offers IT solutions, security systems, motorcycle garage services, and AC maintenance in Maldives. Try Rattehin - our free bill splitting app to easily split restaurant bills with friends and colleagues.">
    <meta name="keywords" content="bill splitter, split bills, restaurant bill calculator, bill sharing app, divide bills, Maldives bill split, Rattehin, motorcycle garage Maldives, AC services Maldives, IT solutions Maldives">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    
    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="#home" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        @if(file_exists(public_path('logo.png')))
                            <img src="{{ asset('logo.png') }}" alt="Micronet Logo" class="h-10 w-auto">
                        @else
                            MicroNET
                        @endif
                    </a>
                </div>

                <!-- Dark Mode Toggle & Staff Login -->
                <div class="flex items-center space-x-3">
                    <!-- Rattehin Button -->
                    <a href="/rattehin" class="flex items-center space-x-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-medium">Rattehin</span>
                    </a>

                    <!-- Staff Login Button (Blue, Iconic, Visible on all devices) -->
                    <a href="/ops" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="text-sm font-medium">Staff</span>
                    </a>

                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <svg id="sunIcon" class="w-5 h-5 text-gray-800 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg id="moonIcon" class="w-5 h-5 text-gray-200 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Landing Page Content -->
    <main>
        <!-- Hero Section with Logo -->
        <section id="home" class="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Logo -->
                <div class="mb-8">
                    @if(file_exists(public_path('logo.png')))
                        <img src="{{ asset('logo.png') }}" alt="Micronet Logo" class="mx-auto h-24 w-auto">
                    @else
                        <h1 class="text-5xl font-bold text-indigo-600 dark:text-indigo-400">MicroNET</h1>
                    @endif
                </div>
                <p class="text-xl text-gray-700 dark:text-gray-300 mb-12">
                    IT, Security, Garage & AC Solutions in Maldives
                </p>
            </div>
        </section>

        <!-- Service Advertisements Section -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Micro Moto Garage Advertisement -->
                    <div class="group relative overflow-hidden rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-orange-500 to-red-600">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                        <div class="relative p-8 md:p-12 text-white">
                            <div class="mb-6">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <h2 class="text-3xl md:text-4xl font-bold mb-4">Micro Moto Garage</h2>
                                <p class="text-lg md:text-xl text-white/90 mb-6">
                                    Professional motorcycle servicing, repairs, and maintenance. We come to you!
                                </p>
                            </div>
                            <a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-6 py-3 bg-white text-orange-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                                Visit Website
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Micro Cool AC Services Advertisement -->
                    <div class="group relative overflow-hidden rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-cyan-500 to-blue-600">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                        <div class="relative p-8 md:p-12 text-white">
                            <div class="mb-6">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                                <h2 class="text-3xl md:text-4xl font-bold mb-4">Micro Cool</h2>
                                <p class="text-lg md:text-xl text-white/90 mb-6">
                                    Expert AC cleaning, maintenance, and installation services. Keep your space cool!
                                </p>
                            </div>
                            <a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-6 py-3 bg-white text-cyan-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg">
                                Visit Website
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">Contact Us</h2>
                
                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Get in Touch</h3>
                        <div class="space-y-4 text-gray-700 dark:text-gray-300">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Address</p>
                                    <p>M. Ithaamuiyge, 10th Floor<br>Alimasmagu Male City</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div>
                        <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Send us a Message</h3>
                        <form id="contactForm" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                                <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone / Email *</label>
                                <input type="text" id="phone" name="phone" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message *</label>
                                <textarea id="message" name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                            </div>
                            <button type="submit" id="submitBtn" class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                Send Message
                            </button>
                            <div id="formMessage" class="hidden mt-4 p-3 rounded-lg text-sm"></div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <!-- Company Info -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Micronet</h3>
                    <p class="text-sm text-gray-400 mb-4">
                        IT, Security, Garage & AC Solutions in Maldives
                    </p>
                    <p class="text-sm text-gray-400">
                        M. Ithaamuiyge, 10th Floor<br>
                        Alimasmagu Male City
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                Micro Moto Garage
                            </a>
                        </li>
                        <li>
                            <a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors">
                                Micro Cool AC Services
                            </a>
                        </li>
                        <li>
                            <a href="/rattehin" class="text-gray-400 hover:text-white transition-colors" title="Free bill splitting app - Split restaurant bills with friends">
                                Rattehin - Bill Splitter App
                            </a>
                        </li>
                        <li>
                            <a href="#contact" class="text-gray-400 hover:text-white transition-colors">
                                Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h3 class="text-white font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="https://facebook.com/MicroMotoGarage/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://x.com/micromotoMV" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-sm text-gray-400">
                    &copy; {{ date('Y') }} Micronet. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Dark Mode Toggle Script -->
    <script>
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        // Check for saved theme preference or default to system preference
        const getTheme = () => {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                return savedTheme;
            }
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };

        // Apply theme
        const setTheme = (theme) => {
            if (theme === 'dark') {
                html.classList.add('dark');
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            } else {
                html.classList.remove('dark');
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
            localStorage.setItem('theme', theme);
        };

        // Initialize theme on page load
        setTheme(getTheme());

        // Toggle theme on button click
        darkModeToggle.addEventListener('click', () => {
            const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
            setTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });

        // Contact Form - Telegram Bot Integration
        const contactForm = document.getElementById('contactForm');
        const formMessage = document.getElementById('formMessage');
        const submitBtn = document.getElementById('submitBtn');

        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const name = formData.get('name');
            const phone = formData.get('phone');
            const message = formData.get('message');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                // Send to Laravel backend endpoint (you'll need to create this)
                const response = await fetch('/contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: name,
                        phone: phone,
                        message: message
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    formMessage.className = 'mt-4 p-3 rounded-lg text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                    formMessage.textContent = 'Message sent successfully! We will get back to you soon.';
                    formMessage.classList.remove('hidden');
                    contactForm.reset();
                } else {
                    throw new Error(data.message || 'Failed to send message');
                }
            } catch (error) {
                formMessage.className = 'mt-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                formMessage.textContent = 'Error sending message. Please try again later.';
                formMessage.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Message';
            }
        });
    </script>
</body>
</html>
