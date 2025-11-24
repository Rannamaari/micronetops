<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Micronet - IT, Security, Garage & AC Solutions in Maldives</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        /* Smooth scroll behavior */
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

                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Home</a>
                    <a href="#about" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">About</a>
                    <a href="#services" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Services</a>
                    <a href="#garage" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Micro Moto Garage</a>
                    <a href="#cool" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Micro Cool</a>
                    <a href="#contact" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Contact</a>
                </div>

                <!-- Dark Mode Toggle, Staff Login & Mobile Menu Button -->
                <div class="flex items-center space-x-4">
                    <!-- Staff Login Button (Small) -->
                    <a href="/ops" class="hidden md:block text-sm px-3 py-1.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        Staff
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

                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden pb-4 space-y-2">
                <a href="#home" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Home</a>
                <a href="#about" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">About</a>
                <a href="#services" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Services</a>
                <a href="#garage" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Micro Moto Garage</a>
                <a href="#cool" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Micro Cool</a>
                <a href="#contact" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Contact</a>
                <a href="/ops" class="block px-3 py-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">Staff</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left: Text Content -->
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                        Connecting Maldives with IT, Security, Garage & AC Solutions
                    </h1>
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-8 leading-relaxed">
                        Founded around 2017, Micronet started as a CCTV & security solutions company, expanding into computer peripherals and network/firewall solutions. Today, we also operate <strong>Micro Moto Garage</strong> for motorcycle services and <strong>Micro Cool</strong> for air-conditioner maintenance.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium shadow-lg">
                            Visit Micro Moto Garage
                        </a>
                        <a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 border-2 border-indigo-600 dark:border-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors font-medium">
                            Visit Micro Cool AC Services
                        </a>
                    </div>
                </div>
                <!-- Right: Illustration Placeholder -->
                <div class="hidden md:block">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
                        <div class="aspect-square flex items-center justify-center bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900 dark:to-blue-900 rounded-lg">
                            <svg class="w-48 h-48 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Micronet Section -->
    <section id="about" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">About Micronet</h2>
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left: Text Content -->
                <div>
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
                        Micronet was formed around <strong>2017</strong> in the Maldives, starting with a focus on <strong>CCTV installation, security systems, and IT infrastructure</strong>. We quickly established ourselves as a trusted provider of computer peripherals, networking gear, and firewall solutions.
                    </p>
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
                        Building on our success, we recently expanded into two new service areas:
                    </p>
                    <ul class="space-y-4 text-gray-700 dark:text-gray-300">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div>
                                <strong class="text-gray-900 dark:text-white">Micro Moto Garage</strong> – Comprehensive motorcycle repairs, servicing, engine oil changes, overhauls, and convenient pick-up & drop-off services.
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                            <div>
                                <strong class="text-gray-900 dark:text-white">Micro Cool</strong> – Professional AC cleaning, maintenance, basic installation, and relocation services with safe, chemical-free cleaning methods.
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- Right: Services List -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Our Core Services</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            CCTV Installation & Configuration
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            Network & Firewall Solutions
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            Computer Peripherals Reselling
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            IT Support & Infrastructure
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            Motorcycle Servicing & Repairs
                        </li>
                        <li class="flex items-center text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 bg-indigo-600 dark:bg-indigo-400 rounded-full mr-3"></span>
                            AC Cleaning & Maintenance
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Overview Section -->
    <section id="services" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">Our Services</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- IT & Security Solutions Card -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-8 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">IT & Security Solutions</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        Professional CCTV installation and configuration, network/firewall reselling, and comprehensive IT support with quality peripherals.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                        <li>• CCTV installation & configuration</li>
                        <li>• Network/firewall reselling</li>
                        <li>• IT support & peripherals</li>
                    </ul>
                </div>

                <!-- Micro Moto Garage Card -->
                <div id="garage" class="bg-white dark:bg-gray-900 rounded-xl p-8 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Micro Moto Garage</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        Full-service motorcycle garage offering repairs, servicing, engine oil changes, overhauls, and convenient pick-up & drop-off services.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                        <li>• Motorcycle servicing & repairs</li>
                        <li>• Engine oil changes, overhauls</li>
                        <li>• Pick-up & drop-off services</li>
                    </ul>
                    <a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-block px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                        Go to garage.micronet.mv →
                    </a>
                </div>

                <!-- Micro Cool Card -->
                <div id="cool" class="bg-white dark:bg-gray-900 rounded-xl p-8 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Micro Cool (AC Services)</h3>
                    <p class="text-gray-700 dark:text-gray-300 mb-6">
                        Expert AC cleaning, maintenance, basic installation, and relocation services using safe, chemical-free cleaning methods.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-600 dark:text-gray-400">
                        <li>• AC cleaning and maintenance</li>
                        <li>• Basic installation and relocation</li>
                        <li>• Chemical-free / safe cleaning</li>
                    </ul>
                    <a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="inline-block px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors text-sm font-medium">
                        Go to cool.micronet.mv →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Location / Map Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">Where to Find Us</h2>
            <p class="text-center text-lg text-gray-700 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Micro Moto Garage is our main physical location where customers can visit for motorcycle services and repairs.
            </p>
            <!-- Google Maps Embed -->
            <div class="rounded-xl overflow-hidden shadow-xl border border-gray-200 dark:border-gray-700">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.251063805545!2d73.51299887473554!3d4.170979895802855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b3f7f70ec752915%3A0xa50f30954afb5a8e!2sMicro%20Moto%20Garage!5e0!3m2!1sen!2smv!4v1764012324664!5m2!1sen!2smv" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    class="w-full">
                </iframe>
            </div>
        </div>
    </section>

    <!-- Quick Links Section -->
    <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-800">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">Quick Links</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                <a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all text-center group">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Micro Moto Garage</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">garage.micronet.mv</p>
                </a>
                <a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all text-center group">
                    <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Micro Cool AC Services</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">cool.micronet.mv</p>
                </a>
                <a href="/ops" class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all text-center group">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Operations System</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Internal System</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Contact / Footer Section -->
    <section id="contact" class="py-20 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white">Contact Us</h2>
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div>
                    <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Get in Touch</h3>
                    <div class="space-y-4 text-gray-700 dark:text-gray-300">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Phone</p>
                                <p>+960 [Phone Number]</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Email</p>
                                <p>info@micronet.mv</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Location</p>
                                <p>Male', Maldives</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Send us a Message</h3>
                    <form class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone / Email</label>
                            <input type="text" id="contact" name="contact" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                            <textarea id="message" name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-white font-semibold mb-4">Micronet</h3>
                    <p class="text-sm text-gray-400">
                        Connecting Maldives with IT, Security, Garage & AC Solutions since 2017.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="https://garage.micronet.mv" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">Micro Moto Garage</a></li>
                        <li><a href="https://cool.micronet.mv" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">Micro Cool AC Services</a></li>
                        <li><a href="/ops" class="hover:text-white transition-colors">Operations System</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Male', Maldives</li>
                        <li>info@micronet.mv</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ date('Y') }} Micronet. All rights reserved.</p>
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

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
