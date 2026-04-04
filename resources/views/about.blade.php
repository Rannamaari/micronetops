<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us — Micronet | Our Story Since 2017</title>
    <meta name="description" content="Learn how Micronet started in 2017 as an IT security company in the Maldives and grew into a full-service technical solutions group including Micro Moto Garage, Micro Cool, and EasyFix.">
    <meta name="keywords" content="Micronet about us, Micronet history, IT company Maldives 2017, Micro Moto Garage, Micro Cool, EasyFix Maldives">
    <meta name="author" content="Micronet">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://micronet.mv/about">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://micronet.mv/about">
    <meta property="og:site_name"   content="Micronet">
    <meta property="og:title"       content="About Micronet — Our Story Since 2017">
    <meta property="og:description" content="Started as an IT security company in 2017, Micronet has grown into a trusted multi-service team covering IT, AC, motorcycle repairs, and home services across the Maldives.">
    <meta property="og:image"       content="https://micronet.mv/ogimage.png">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"    content="Micronet — Our Story Since 2017">
    <meta property="og:locale"      content="en_US">

    <!-- Twitter / X Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@@micromotoMV">
    <meta name="twitter:title"       content="About Micronet — Our Story Since 2017">
    <meta name="twitter:description" content="Started in 2017 as an IT security company. Today we run Micronet, Micro Moto Garage, Micro Cool, and EasyFix across the Maldives.">
    <meta name="twitter:image"       content="https://micronet.mv/ogimage.png">

    <!-- Theme colour -->
    <meta name="theme-color" content="#DC2626">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- JSON-LD -->
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AboutPage",
        "name": "About Micronet",
        "url": "https://micronet.mv/about",
        "description": "Micronet was founded in 2017 in Male, Maldives as an IT and security solutions company. It has since expanded into Micro Moto Garage, Micro Cool, and EasyFix.",
        "mainEntity": {
            "@type": "Organization",
            "name": "Micronet",
            "url": "https://micronet.mv",
            "logo": "https://micronet.mv/logo.png",
            "foundingDate": "2017",
            "foundingLocation": "Male, Maldives",
            "telephone": "+9609996210",
            "email": "hi@micronet.mv",
            "sameAs": [
                "https://www.facebook.com/micronetmaldives",
                "https://www.instagram.com/micronetmv/",
                "https://x.com/micromotoMV"
            ]
        }
    }
    </script>
    @endverbatim

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        .hero-bg {
            background-color: #080808;
            background-image:
                radial-gradient(circle at 15% 55%, rgba(220, 38, 38, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 40%);
        }

        .grid-overlay {
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Timeline connector line */
        .timeline-line::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 44px;
            bottom: -24px;
            width: 2px;
            background: linear-gradient(to bottom, #DC2626, #e5e7eb);
        }

        .nav-link { position: relative; }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -3px; left: 0;
            width: 0; height: 2px;
            background: #DC2626;
            transition: width 0.25s ease;
        }
        .nav-link:hover::after { width: 100%; }

        .footer-bg { background-color: #080808; }
    </style>
</head>

<body class="font-sans antialiased bg-white text-gray-900"
      x-data="{ mobileOpen: false, scrolled: false }"
      @scroll.window="scrolled = (window.pageYOffset > 30)">

<!-- ================================================================
     STICKY HEADER
================================================================ -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-md' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                <img src="{{ asset('logo.png') }}" alt="Micronet" class="h-9 w-auto"
                     :class="scrolled ? 'brightness-100' : 'brightness-0 invert'">
            </a>

            <!-- Desktop nav -->
            <nav class="hidden lg:flex items-center space-x-7">
                <a href="{{ route('home') }}" class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Home</a>
                <a href="{{ route('home') }}#services" class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Services</a>
                <a href="{{ route('about') }}" class="nav-link text-sm font-medium text-red-500 border-b-2 border-red-500 pb-0.5">About Us</a>
                <a href="{{ route('home') }}#contact" class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Contact</a>
                <a href="{{ route('login') }}" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200">Login</a>
            </nav>

            <!-- Mobile button -->
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 rounded-lg"
                    :class="scrolled ? 'text-gray-700' : 'text-white'">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden bg-white border-t border-gray-100 shadow-2xl">
        <div class="px-4 py-3 space-y-1">
            <a @click="mobileOpen=false" href="{{ route('home') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Home</a>
            <a @click="mobileOpen=false" href="{{ route('home') }}#services" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Services</a>
            <a @click="mobileOpen=false" href="{{ route('about') }}" class="block px-4 py-3 text-red-600 bg-red-50 rounded-xl font-medium">About Us</a>
            <a @click="mobileOpen=false" href="{{ route('home') }}#contact" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Contact</a>
            <div class="pt-2 pb-1 border-t border-gray-100">
                <a href="{{ route('login') }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-3 rounded-xl transition-colors">Login</a>
            </div>
        </div>
    </div>
</header>


<!-- ================================================================
     HERO
================================================================ -->
<section class="hero-bg grid-overlay pt-32 pb-20 lg:pt-40 lg:pb-28 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-700 opacity-[0.05] rounded-full blur-[80px] pointer-events-none translate-x-1/3 -translate-y-1/3"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center bg-red-600/10 border border-red-600/25 rounded-full px-4 py-1.5 mb-7">
            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
            <span class="text-red-400 text-xs font-semibold uppercase tracking-wider">Est. 2017 — Maldives</span>
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
            Built in the Maldives.<br>
            <span class="text-red-500">Built for the Maldives.</span>
        </h1>

        <p class="text-gray-400 text-lg sm:text-xl leading-relaxed max-w-2xl mx-auto">
            We started as a small IT security company in 2017. Seven years later, we're a team of technicians, mechanics, and problem-solvers serving homes and businesses across the country.
        </p>
    </div>
</section>


<!-- ================================================================
     OUR STORY
================================================================ -->
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">Our Story</span>
            <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mt-3">How It All Started</h2>
        </div>

        <!-- Story text -->
        <div class="prose-like max-w-2xl mx-auto text-center mb-20">
            <p class="text-gray-600 text-lg leading-relaxed mb-5">
                Micronet was founded in <strong class="text-gray-900">2017</strong> with a single focus — IT and security solutions. We installed networks, set up CCTV systems, and supported businesses that needed reliable technical help in Malé.
            </p>
            <p class="text-gray-600 text-lg leading-relaxed mb-5">
                Over time, the same customers who called us for networking started asking for help with other things — a broken AC, a motorcycle that needed servicing, a website for their business. The need was real, so we showed up.
            </p>
            <p class="text-gray-600 text-lg leading-relaxed">
                That's how Micronet became more than just an IT company. One step at a time, one customer at a time, we grew into a team that handles the full range of technical and repair needs — because in the Maldives, reliable help shouldn't be hard to find.
            </p>
        </div>

        <!-- Timeline -->
        <div class="max-w-2xl mx-auto">
            <h3 class="text-lg font-black text-gray-900 mb-10 text-center uppercase tracking-wider text-sm text-red-600">Our Journey</h3>

            <div class="space-y-0">

                <!-- 2017 -->
                <div class="relative pl-14 pb-10 timeline-line">
                    <div class="absolute left-0 top-0 w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-900/30 flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-red-600 font-black text-xl">2017</span>
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Founded</span>
                        </div>
                        <h4 class="text-gray-900 font-bold text-base mb-1">Micronet — IT & Security</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Started with a clear mission: provide reliable IT support and security solutions — networking, CCTV, computer repairs, and web development — to homes and businesses in Malé.
                        </p>
                    </div>
                </div>

                <!-- Micro Moto -->
                <div class="relative pl-14 pb-10 timeline-line">
                    <div class="absolute left-0 top-0 w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-gray-900 font-black text-xl">Micro Moto Garage</span>
                            <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Expanded</span>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Launched Micro Moto Garage to serve the growing demand for professional motorcycle servicing, diagnostics, and repairs. A trusted garage for riders across Malé.
                        </p>
                    </div>
                </div>

                <!-- Micro Cool -->
                <div class="relative pl-14 pb-10 timeline-line">
                    <div class="absolute left-0 top-0 w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.36 18.36l.707.707M1 12h2M21 12h2M4.22 19.78l.707-.707M18.36 5.64l.707-.707M12 7a5 5 0 110 10A5 5 0 0112 7z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-blue-600 font-black text-xl">Micro Cool</span>
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Expanded</span>
                        </div>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Launched Micro Cool to provide professional air conditioning installation, servicing, and repair. Certified technicians handling residential and commercial AC systems.
                        </p>
                    </div>
                </div>

                <!-- EasyFix -->
                <div class="relative pl-14">
                    <div class="absolute left-0 top-0 w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center shadow-lg shadow-red-900/30 flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="bg-red-50 rounded-2xl p-6 border border-red-100">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ asset('easyfix-logo.png') }}" alt="EasyFix logo" class="h-6 w-auto">
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Latest</span>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Built EasyFix to solve a simple problem — finding reliable help for small home repairs in Malé is too hard. AC repair, plumbing, electrical, and handyman services, available when you need them.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<!-- ================================================================
     WHERE WE ARE TODAY
================================================================ -->
<section class="py-20 lg:py-24 bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="text-red-500 font-semibold text-xs uppercase tracking-widest">Today</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white mt-3 mb-4">One Team. Four Brands. One Mission.</h2>
            <p class="text-gray-400 text-lg max-w-xl mx-auto">Everything we've built points to the same goal — be the most reliable technical service provider in the Maldives.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Micronet -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-red-700/50 transition-colors">
                <div class="mb-5">
                    <img src="{{ asset('logo.png') }}" alt="Micronet logo" class="h-8 w-auto brightness-0 invert">
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">IT support, CCTV, networking, web development, and computer repairs. The core of everything we do.</p>
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <span class="text-red-500 text-xs font-semibold uppercase tracking-wide">Since 2017</span>
                </div>
            </div>

            <!-- Micro Moto -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-gray-600 transition-colors">
                <div class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-white font-bold mb-2">Micro Moto Garage</h3>
                <p class="text-gray-400 text-sm leading-relaxed">Motorcycle servicing, diagnostics, and repairs by experienced mechanics.</p>
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <a href="https://micromoto.mv" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-red-400 text-xs font-semibold uppercase tracking-wide transition-colors">micromoto.mv ↗</a>
                </div>
            </div>

            <!-- Micro Cool -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-blue-700/50 transition-colors">
                <div class="w-10 h-10 bg-blue-900/40 rounded-xl flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.36 18.36l.707.707M1 12h2M21 12h2M4.22 19.78l.707-.707M18.36 5.64l.707-.707M12 7a5 5 0 110 10A5 5 0 0112 7z"/>
                    </svg>
                </div>
                <h3 class="text-white font-bold mb-2">Micro Cool</h3>
                <p class="text-gray-400 text-sm leading-relaxed">AC installation, servicing, and repair for homes, offices, and commercial spaces.</p>
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <a href="https://microcool.mv" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-blue-400 text-xs font-semibold uppercase tracking-wide transition-colors">microcool.mv ↗</a>
                </div>
            </div>

            <!-- EasyFix -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-red-700/50 transition-colors">
                <div class="mb-5">
                    <img src="{{ asset('easyfix-logo.png') }}" alt="EasyFix logo" class="h-7 w-auto brightness-0 invert">
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">Home repair services — AC, plumbing, electrical, and handyman — available when you need them.</p>
                <div class="mt-4 pt-4 border-t border-gray-800">
                    <a href="https://easyfix.mv" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-red-400 text-xs font-semibold uppercase tracking-wide transition-colors">easyfix.mv ↗</a>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     VALUES
================================================================ -->
<section class="py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">What Drives Us</span>
            <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mt-3">Our Values</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Honesty</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">We tell you what the problem is, what it costs, and what it will take to fix it. No surprises.</p>
                </div>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Speed</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">We respond fast and work efficiently because delays cost people time and money.</p>
                </div>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">People First</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Every customer gets the same level of care — whether it's a small home repair or a large installation.</p>
                </div>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Fair Pricing</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">We charge what's fair. Quality service doesn't need to come with an inflated price tag.</p>
                </div>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m1.636 6.364l-.707-.707M12 20v1M7.343 4.343l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Keep Learning</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Technology moves fast. We stay current so our customers always get the best solutions available.</p>
                </div>
            </div>

            <div class="flex gap-4 items-start">
                <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Built for the Maldives</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">We understand the local context — the islands, the constraints, and what it takes to deliver here.</p>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     CTA
================================================================ -->
<section class="bg-gray-950 py-16 lg:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-black text-white mb-4">Ready to work with us?</h2>
        <p class="text-gray-400 mb-8">Call us or send a message — we're always happy to help.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:9996210"
               class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold px-8 py-4 rounded-2xl text-base transition-all duration-200 shadow-xl shadow-red-900/30">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Call 9996210
            </a>
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center border-2 border-gray-700 hover:border-red-600 text-gray-300 hover:text-white font-bold px-8 py-4 rounded-2xl text-base transition-all duration-200">
                ← Back to Home
            </a>
        </div>
    </div>
</section>


<!-- ================================================================
     FOOTER
================================================================ -->
<footer class="footer-bg border-t border-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-600">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo.png') }}" alt="Micronet" class="h-6 w-auto brightness-0 invert opacity-50">
            <span>&copy; {{ date('Y') }} Micronet. All rights reserved.</span>
        </div>
        <div class="flex space-x-5">
            <a href="{{ route('about') }}" class="hover:text-gray-400 transition-colors">About Us</a>
            <a href="{{ route('privacy.policy') }}" class="hover:text-gray-400 transition-colors">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-gray-400 transition-colors">Terms</a>
        </div>
    </div>
</footer>

</body>
</html>
