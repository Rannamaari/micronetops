<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Micronet — Trusted IT, Repairs & Technical Services in the Maldives</title>
    <meta name="description" content="From CCTV installation and networking to air conditioning and motorcycle repairs — Micronet delivers reliable technical solutions for homes and businesses across the Maldives.">
    <meta name="keywords" content="Micronet, IT support Maldives, CCTV installation Male, networking Maldives, AC repair, motorcycle repair, web development, EasyFix, Micro Moto Garage, Micro Cool, technical services Maldives">
    <meta name="author" content="Micronet">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://micronet.mv/">

    <!-- Open Graph — Facebook, WhatsApp, LinkedIn -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="https://micronet.mv/">
    <meta property="og:site_name"   content="Micronet">
    <meta property="og:title"       content="Micronet — Trusted IT, Repairs & Technical Services in the Maldives">
    <meta property="og:description" content="From CCTV installation and networking to air conditioning and motorcycle repairs — Micronet delivers reliable solutions for homes and businesses across the Maldives.">
    <meta property="og:image"       content="https://micronet.mv/ogimage.png">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt"    content="Micronet — IT, Repairs & Technical Services in the Maldives">
    <meta property="og:locale"      content="en_US">

    <!-- Twitter / X Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@@micromotoMV">
    <meta name="twitter:creator"     content="@@micromotoMV">
    <meta name="twitter:title"       content="Micronet — Trusted IT, Repairs & Technical Services in the Maldives">
    <meta name="twitter:description" content="From CCTV installation and networking to air conditioning and motorcycle repairs — Micronet delivers reliable solutions across the Maldives.">
    <meta name="twitter:image"       content="https://micronet.mv/ogimage.png">
    <meta name="twitter:image:alt"   content="Micronet — IT & Technical Services">

    <!-- Theme colour (browser chrome on mobile) -->
    <meta name="theme-color" content="#DC2626">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- JSON-LD Structured Data -->
    @verbatim
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "Micronet",
        "url": "https://micronet.mv",
        "logo": "https://micronet.mv/logo.png",
        "image": "https://micronet.mv/ogimage.png",
        "description": "Micronet delivers reliable IT support, CCTV installation, networking, AC services, and motorcycle repairs for homes and businesses across the Maldives.",
        "telephone": "+9609996210",
        "email": "hi@micronet.mv",
        "foundingDate": "2017",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "M. Ithaamuiyge 1, 10th Floor, Alimasmagu",
            "addressLocality": "Male",
            "addressCountry": "MV"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "4.1755",
            "longitude": "73.5093"
        },
        "sameAs": [
            "https://www.facebook.com/micronetmaldives",
            "https://www.instagram.com/micronetmv/",
            "https://x.com/micromotoMV"
        ],
        "openingHoursSpecification": {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Sunday","Monday","Tuesday","Wednesday","Thursday"],
            "opens": "08:00",
            "closes": "18:00"
        },
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "Technical Services",
            "itemListElement": [
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "CCTV Installation" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Networking & IT Support" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Website & Web App Development" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Computer & Device Repair" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Air Conditioning Services" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Motorcycle Repair & Maintenance" } },
                { "@type": "Offer", "itemOffered": { "@type": "Service", "name": "Home & Office Technical Support" } }
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

        /* Hero background */
        .hero-bg {
            background-color: #080808;
            background-image:
                radial-gradient(circle at 15% 55%, rgba(220, 38, 38, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 15%, rgba(37, 99, 235, 0.10) 0%, transparent 40%),
                radial-gradient(circle at 65% 85%, rgba(220, 38, 38, 0.07) 0%, transparent 35%);
        }

        /* Subtle grid overlay */
        .grid-overlay {
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* Floating badge animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-8px); }
        }
        .float { animation: float 5s ease-in-out infinite; }
        .float-delay { animation: float 5s ease-in-out 1.5s infinite; }

        /* Nav underline effect */
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

        /* Service card icon transition */
        .service-card .svc-icon {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .service-card:hover .svc-icon {
            background-color: #DC2626 !important;
            color: #fff !important;
        }

        /* Testimonial top border */
        .testimonial { border-top: 3px solid #DC2626; }

        /* EasyFix gradient */
        .easyfix-bg {
            background: linear-gradient(135deg, #DC2626 0%, #7f1d1d 100%);
        }

        /* CTA gradient */
        .cta-bg {
            background: linear-gradient(135deg, #0f0f0f 0%, #1c1c1c 100%);
        }

        /* Footer bg */
        .footer-bg { background-color: #080808; }

        /* Smooth scroll offset for fixed header */
        :target { scroll-margin-top: 80px; }
    </style>
</head>

<body class="font-sans antialiased bg-white text-gray-900"
      x-data="{ mobileOpen: false, scrolled: false }"
      @scroll.window="scrolled = (window.pageYOffset > 30)">

<!-- ================================================================
     STICKY HEADER / NAVBAR
================================================================ -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        :class="scrolled
            ? 'bg-white/95 backdrop-blur-md shadow-md'
            : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            <!-- ── Logo ── -->
            <a href="#" class="flex items-center flex-shrink-0">
                <img src="{{ asset('logo.png') }}" alt="Micronet" class="h-9 w-auto"
                     :class="scrolled ? 'brightness-100' : 'brightness-0 invert'">
            </a>

            <!-- ── Desktop nav ── -->
            <nav class="hidden lg:flex items-center space-x-7">
                <a href="#services"  class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Services</a>
                <a href="#why-us"   class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Why Micronet</a>
                <a href="#reviews"  class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Reviews</a>
                <a href="#contact"  class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">Contact</a>
                <a href="{{ route('about') }}" class="nav-link text-sm font-medium transition-colors" :class="scrolled ? 'text-gray-700 hover:text-red-600' : 'text-gray-300 hover:text-white'">About Us</a>
                <a href="{{ route('login') }}" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-md shadow-red-900/20">
                    Login
                </a>
            </nav>

            <!-- ── Mobile menu button ── -->
            <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded-lg transition-colors"
                    :class="scrolled ? 'text-gray-700 hover:bg-gray-100' : 'text-white hover:bg-white/10'">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Mobile Menu ── -->
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden bg-white border-t border-gray-100 shadow-2xl">
        <div class="px-4 py-3 space-y-1">
            <a @click="mobileOpen=false" href="#services" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Services</a>
            <a @click="mobileOpen=false" href="#why-us"  class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Why Micronet</a>
            <a @click="mobileOpen=false" href="#reviews" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Reviews</a>
            <a @click="mobileOpen=false" href="#contact" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">Contact</a>
            <a @click="mobileOpen=false" href="{{ route('about') }}" class="block px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-xl font-medium transition-colors">About Us</a>
            <div class="pt-2 pb-1 border-t border-gray-100">
                <a href="{{ route('login') }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-3 rounded-xl transition-colors">Login</a>
            </div>
        </div>
    </div>
</header>


<!-- ================================================================
     HERO SECTION
================================================================ -->
<section class="hero-bg grid-overlay min-h-screen flex items-center relative overflow-hidden">

    <!-- Ambient blobs -->
    <div class="absolute top-1/3 right-0 w-[500px] h-[500px] bg-red-700 opacity-[0.06] rounded-full blur-[80px] pointer-events-none transform translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-700 opacity-[0.05] rounded-full blur-[80px] pointer-events-none transform -translate-x-1/3"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-24 lg:py-0 w-full">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            <!-- ── Text ── -->
            <div class="text-center lg:text-left">

                <!-- Pill badge -->
                <div class="inline-flex items-center bg-red-600/10 border border-red-600/25 rounded-full px-4 py-1.5 mb-7">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-red-400 text-xs font-semibold uppercase tracking-wider">Serving the Maldives</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] xl:text-6xl font-black text-white leading-[1.1] mb-6">
                    Your Trusted Partner for
                    <span class="text-red-500"> IT, Repairs &amp;</span>
                    <span class="text-white"> Technical&nbsp;Services</span>
                    <span class="block text-gray-400 text-3xl sm:text-4xl lg:text-[2.5rem] mt-2 font-extrabold">in the Maldives</span>
                </h1>

                <p class="text-gray-400 text-lg sm:text-xl leading-relaxed mb-10 max-w-xl mx-auto lg:mx-0">
                    From CCTV installation and networking to air conditioning and motorcycle repairs — Micronet delivers reliable solutions for homes and businesses.
                </p>

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="#contact"
                       class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white font-bold px-8 py-4 rounded-2xl text-base transition-all duration-200 shadow-xl shadow-red-900/30 hover:shadow-red-900/50 hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Contact Us
                    </a>
                    <a href="#services"
                       class="inline-flex items-center justify-center border-2 border-gray-700 hover:border-red-600 text-gray-300 hover:text-white font-bold px-8 py-4 rounded-2xl text-base transition-all duration-200">
                        View Services
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                </div>

                <!-- Quick stats -->
                <div class="mt-14 flex items-center justify-center lg:justify-start divide-x divide-gray-800 space-x-0">
                    <div class="pr-8 text-center lg:text-left">
                        <div class="text-3xl font-black text-white">500+</div>
                        <div class="text-gray-500 text-xs mt-1">Customers Served</div>
                    </div>
                    <div class="px-8 text-center lg:text-left">
                        <div class="text-3xl font-black text-white">7+</div>
                        <div class="text-gray-500 text-xs mt-1">Services Offered</div>
                    </div>
                    <div class="pl-8 text-center lg:text-left">
                        <div class="text-3xl font-black text-white">5★</div>
                        <div class="text-gray-500 text-xs mt-1">Customer Rating</div>
                    </div>
                </div>
            </div>

            <!-- ── Visual ── -->
            <div class="hidden lg:flex justify-center items-center relative">

                <!-- Floating badge — top left -->
                <div class="float absolute -top-4 -left-6 z-10">
                    <div class="bg-white/8 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center space-x-3 shadow-xl">
                        <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <!-- Camera icon -->
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Now Available</div>
                            <div class="text-sm font-bold text-white">CCTV Installation</div>
                        </div>
                    </div>
                </div>

                <!-- Central visual ring -->
                <div class="relative w-80 h-80">
                    <!-- Outer ring -->
                    <div class="absolute inset-0 rounded-full border-2 border-red-600/15 animate-spin" style="animation-duration:20s;"></div>
                    <!-- Inner ring -->
                    <div class="absolute inset-8 rounded-full border border-red-600/20 animate-spin" style="animation-duration:12s; animation-direction: reverse;"></div>
                    <!-- Core card -->
                    <div class="absolute inset-12 bg-gradient-to-br from-red-600/15 to-transparent border border-red-600/20 rounded-3xl flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-red-500 opacity-70 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Micronet</span>
                    </div>
                    <!-- Orbit dots -->
                    <div class="absolute top-2 left-1/2 -translate-x-1/2 w-3 h-3 bg-red-600 rounded-full shadow-lg shadow-red-600/50"></div>
                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-2 h-2 bg-blue-600 rounded-full shadow-lg shadow-blue-600/50"></div>
                    <div class="absolute left-2 top-1/2 -translate-y-1/2 w-2 h-2 bg-red-400 rounded-full"></div>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 bg-gray-600 rounded-full"></div>
                </div>

                <!-- Floating badge — bottom right -->
                <div class="float-delay absolute -bottom-2 -right-4 z-10">
                    <div class="bg-white/8 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center space-x-3 shadow-xl">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <!-- Shield check -->
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Trusted by</div>
                            <div class="text-sm font-bold text-white">500+ Customers</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-gray-600 animate-bounce">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>


<!-- ================================================================
     SERVICES SECTION
================================================================ -->
<section id="services" class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section header -->
        <div class="text-center mb-10">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">What We Do</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mt-3 mb-4">Our Services</h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">We fix what breaks, build what's needed, and support what keeps you running.</p>
        </div>


        <!-- Grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

            <!-- CCTV -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">CCTV Installation</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Professional CCTV setup and monitoring for homes, offices, and businesses.</p>
            </div>

            <!-- Networking -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Networking & IT Support</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Network setup, troubleshooting, and ongoing IT support for businesses and homes.</p>
            </div>

            <!-- Web Dev -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Website & Web App Development</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Custom websites and web apps built to grow your business online.</p>
            </div>

            <!-- Computer Repair -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Computer & Device Repair</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Laptop, PC, and device repairs with fast turnarounds and reliable diagnostics.</p>
            </div>

            <!-- AC Services -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.36 18.36l.707.707M1 12h2M21 12h2M4.22 19.78l.707-.707M18.36 5.64l.707-.707M12 7a5 5 0 110 10A5 5 0 0112 7z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Air Conditioning Services</h3>
                <p class="text-gray-500 text-sm leading-relaxed">AC installation, servicing, repair, and maintenance by certified technicians.</p>
            </div>

            <!-- Motorcycle -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Motorcycle Repair & Maintenance</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Full motorcycle servicing, diagnostics, and repairs at Micro Moto Garage.</p>
            </div>

            <!-- Home Support — spans 2 cols on xl -->
            <div class="service-card group bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-default sm:col-span-2 lg:col-span-1">
                <div class="svc-icon w-14 h-14 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Home & Office Technical Support</h3>
                <p class="text-gray-500 text-sm leading-relaxed">On-site technical assistance for homes and offices — from setup to maintenance.</p>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     EASYFIX SECTION
================================================================ -->
<section id="easyfix" class="easyfix-bg py-20 lg:py-28 relative overflow-hidden">
    <!-- Ambient -->
    <div class="absolute top-0 left-0 w-[400px] h-[400px] bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-black/20 rounded-full blur-3xl translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            <!-- ── Content ── -->
            <div>
                <div class="inline-flex items-center bg-white/15 rounded-full px-4 py-1.5 mb-6">
                    <span class="text-white/80 text-xs font-semibold uppercase tracking-wider">⚡ A Micronet Brand</span>
                </div>

                <img src="{{ asset('easyfix-logo.png') }}"
                     alt="EasyFix logo"
                     class="h-20 sm:h-24 lg:h-28 w-auto mb-6 brightness-0 invert drop-shadow-lg">

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6 leading-tight">
                    Reliable Help<br class="hidden sm:block"> When You Need It
                </h2>

                <p class="text-red-100 text-lg leading-relaxed mb-4">
                    We started EasyFix because finding reliable help for small repairs in Malé shouldn't be a headache.
                </p>
                <p class="text-red-200/80 text-base leading-relaxed mb-9">
                    Whether it's a leaky tap at 8 PM or an AC that won't cool before guests arrive, we've got you covered.
                </p>

                <a href="https://easyfix.mv" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center bg-white text-red-600 hover:bg-red-50 font-bold px-8 py-4 rounded-2xl text-base transition-all duration-200 shadow-2xl shadow-red-950/40 hover:-translate-y-0.5">
                    Visit EasyFix
                    <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <!-- ── Service mini-cards ── -->
            <div class="grid grid-cols-2 gap-3">

                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 text-white hover:bg-white/15 transition-colors">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.36 18.36l.707.707M1 12h2M21 12h2M4.22 19.78l.707-.707M18.36 5.64l.707-.707M12 7a5 5 0 110 10A5 5 0 0112 7z"/>
                        </svg>
                    </div>
                    <div class="font-bold text-sm mb-0.5">AC Repair</div>
                    <div class="text-red-200 text-xs">Fast cooling solutions</div>
                </div>

                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 text-white hover:bg-white/15 transition-colors">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div class="font-bold text-sm mb-0.5">Plumbing</div>
                    <div class="text-red-200 text-xs">Leaks, pipes & fixtures</div>
                </div>

                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 text-white hover:bg-white/15 transition-colors">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="font-bold text-sm mb-0.5">Electrical</div>
                    <div class="text-red-200 text-xs">Wiring, switches & faults</div>
                </div>

                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 text-white hover:bg-white/15 transition-colors">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="font-bold text-sm mb-0.5">Emergency Repairs</div>
                    <div class="text-red-200 text-xs">Available when you need us</div>
                </div>

                <!-- Handyman — full width -->
                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 text-white hover:bg-white/15 transition-colors col-span-2">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-sm mb-0.5">Handyman Services</div>
                            <div class="text-red-200 text-xs">Furniture assembly, general repairs & more</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<!-- ================================================================
     WHO WE SERVE
================================================================ -->
<section id="who-we-serve" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section header -->
        <div class="text-center mb-6">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">Our Customers</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mt-3 mb-4">Who We Serve</h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">We proudly support customers across the Maldives — from homes and small businesses to large organizations.</p>
        </div>

        <!-- Highlight banner -->
        <div class="flex items-center justify-center mb-14">
            <div class="inline-flex items-center gap-3 bg-red-50 border border-red-100 rounded-2xl px-5 py-3 max-w-xl text-center">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-red-700 text-sm font-medium leading-snug">
                    From homes in Malé to resorts across the islands — Micronet supports customers everywhere.
                </p>
            </div>
        </div>

        <!-- Customer type grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- Home Owners -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Home Owners</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Reliable technical support, repairs, and installations for everyday home needs.</p>
                </div>
            </div>

            <!-- Guest Houses -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Guest Houses</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Fast maintenance and support to keep your guests comfortable and operations running smoothly.</p>
                </div>
            </div>

            <!-- Offices -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Offices</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Networking, IT support, CCTV, and technical solutions for modern workplaces.</p>
                </div>
            </div>

            <!-- Resorts -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Resorts</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Professional installation and maintenance services for large properties and hospitality operations.</p>
                </div>
            </div>

            <!-- Hospitals -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16M7 10h10M12 3v7m0 4h.01M9 16h6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Hospitals</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Reliable technical support and system maintenance for critical healthcare environments.</p>
                </div>
            </div>

            <!-- Clinics -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Clinics</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Quick and dependable service to ensure medical facilities operate without interruption.</p>
                </div>
            </div>

            <!-- Shops & Retail -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Shops & Retail Businesses</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Security systems, networking, and maintenance services to support daily business operations.</p>
                </div>
            </div>

            <!-- Restaurants & Cafés -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Restaurants & Cafés</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Technical and repair services to keep kitchens, cooling systems, and operations running smoothly.</p>
                </div>
            </div>

            <!-- Schools & Institutions -->
            <div class="group flex items-start gap-5 bg-gray-50 hover:bg-white border border-transparent hover:border-red-100 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 group-hover:bg-red-600 rounded-2xl flex items-center justify-center transition-colors duration-300">
                    <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Schools & Institutions</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">IT infrastructure, security systems, and technical support for education environments.</p>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     WHY CHOOSE MICRONET
================================================================ -->
<section id="why-us" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">Our Commitment</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mt-3 mb-4">Why Choose Micronet</h2>
            <p class="text-gray-500 text-lg max-w-xl mx-auto">We're committed to quality, speed, and reliability you can count on — every time.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- Reliable -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-red-50 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mb-5 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-red-900/20 group-hover:-translate-y-1">
                    <svg class="w-9 h-9 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-2">Reliable Service</h3>
                <p class="text-gray-500 text-sm leading-relaxed">We show up when we say we will. Dependable service you can count on every time.</p>
            </div>

            <!-- Affordable -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-red-50 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mb-5 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-red-900/20 group-hover:-translate-y-1">
                    <svg class="w-9 h-9 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-2">Affordable Pricing</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Fair, transparent pricing with no hidden charges. Quality service at the right price.</p>
            </div>

            <!-- Fast -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-red-50 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mb-5 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-red-900/20 group-hover:-translate-y-1">
                    <svg class="w-9 h-9 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-2">Fast Response</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Quick turnaround and rapid response times because your time matters to us.</p>
            </div>

            <!-- Experienced -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-red-50 group-hover:bg-red-600 rounded-2xl flex items-center justify-center mb-5 transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:shadow-red-900/20 group-hover:-translate-y-1">
                    <svg class="w-9 h-9 text-red-600 group-hover:text-white transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-2">Experienced Technicians</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Skilled professionals with hands-on experience across all service areas.</p>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     REVIEWS / TESTIMONIALS
================================================================ -->
<section id="reviews" class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <span class="text-red-600 font-semibold text-xs uppercase tracking-widest">Testimonials</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-900 mt-3 mb-4">What Our Customers Say</h2>
            <p class="text-gray-500 text-lg max-w-xl mx-auto">Trusted by hundreds of homes and businesses across the Maldives.</p>
        </div>

        <!-- Stars summary -->
        <div class="flex items-center justify-center space-x-3 mb-12">
            <div class="flex text-yellow-400 space-x-0.5">
                @for ($i = 0; $i < 5; $i++)
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            <span class="text-2xl font-black text-gray-900">5.0</span>
            <span class="text-gray-400 text-sm">Excellent customer reviews</span>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Review 1 -->
            <div class="testimonial bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex text-yellow-400 space-x-0.5 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-5 italic">
                    "Very fast and professional service. They installed our CCTV system quickly and the quality is excellent. Highly recommended!"
                </p>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-700 font-bold text-sm flex-shrink-0">AH</div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">Ahmed Hassan</div>
                        <div class="text-gray-400 text-xs">Business Owner, Malé</div>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="testimonial bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex text-yellow-400 space-x-0.5 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-5 italic">
                    "Our office network was a mess. Micronet sorted everything out in one visit — clean cabling, faster speeds, and great support. Will definitely use again!"
                </p>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">FM</div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">Fathimath Mohamed</div>
                        <div class="text-gray-400 text-xs">Office Manager, Malé</div>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="testimonial bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex text-yellow-400 space-x-0.5 mb-4">
                    @for ($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-5 italic">
                    "Took my motorbike to Micro Moto Garage. The guys were honest, thorough, and the price was very fair. My bike runs like new. 10/10!"
                </p>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-700 font-bold text-sm flex-shrink-0">IR</div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">Ibrahim Rasheed</div>
                        <div class="text-gray-400 text-xs">Customer, Malé</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================================================================
     CALL TO ACTION
================================================================ -->
<section id="contact" class="cta-bg py-20 lg:py-28 relative overflow-hidden">
    <!-- Ambient blobs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-700/5 rounded-full blur-3xl translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-700/5 rounded-full blur-3xl -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">

        <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-8 shadow-xl shadow-red-900/40">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>

        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-5 leading-tight">
            Need Help With Repairs or<br class="hidden sm:block"> Technical Services?
        </h2>
        <p class="text-gray-400 text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            Call us today and let our team take care of the rest. Available for homes and businesses across the Maldives.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="tel:9996210"
               class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-black px-10 py-5 rounded-2xl text-xl transition-all duration-200 shadow-2xl shadow-red-900/50 hover:shadow-red-900/70 hover:-translate-y-0.5 hover:scale-[1.02]">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Call Now — 9996210
            </a>

            <a href="https://wa.me/9609996210" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center border-2 border-gray-700 hover:border-green-500 text-gray-300 hover:text-green-400 font-bold px-8 py-4 rounded-2xl text-lg transition-all duration-200">
                <!-- WhatsApp icon -->
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp Us
            </a>
        </div>

        <!-- Contact details strip -->
        <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-2xl mx-auto">
            <div class="text-center">
                <div class="text-gray-600 text-xs uppercase tracking-wider mb-1">Phone</div>
                <div class="text-white font-semibold">9996210</div>
            </div>
            <div class="text-center sm:border-x sm:border-gray-800">
                <div class="text-gray-600 text-xs uppercase tracking-wider mb-1">Email</div>
                <div class="text-white font-semibold">hi@micronet.mv</div>
            </div>
            <div class="text-center">
                <div class="text-gray-600 text-xs uppercase tracking-wider mb-1">Location</div>
                <div class="text-white font-semibold">M. Ithaamuiyge 1, 10th Floor, Alimasmagu</div>
            </div>
        </div>
    </div>
</section>


<!-- ================================================================
     HIRING STRIP
================================================================ -->
<div class="bg-gray-950 border-y border-gray-800 py-5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-center sm:text-left">
                <span class="flex-shrink-0 w-8 h-8 bg-red-600/15 border border-red-600/25 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6a4 4 0 11-8 0 4 4 0 018 0zm-8 7a4 4 0 008 0"/>
                    </svg>
                </span>
                <p class="text-gray-300 text-sm">
                    <span class="text-white font-semibold">We're hiring!</span>
                    &nbsp;Micronet is looking for talented people in the field of <span class="text-red-400 font-medium">Networking</span>. Send your CV and join the team.
                </p>
            </div>
            <a href="mailto:hi@micronet.mv?subject=CV%20Application%20—%20Networking"
               class="flex-shrink-0 inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors duration-200 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Your CV
            </a>
        </div>
    </div>
</div>


<!-- ================================================================
     FOOTER
================================================================ -->
<footer class="footer-bg text-gray-400">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

            <!-- Brand + social -->
            <div class="lg:col-span-2">
                <div class="mb-5">
                    <img src="{{ asset('logo.png') }}" alt="Micronet" class="h-9 w-auto brightness-0 invert">
                </div>
                <p class="text-sm leading-relaxed mb-6 max-w-xs">
                    Your trusted partner for IT support, repairs, and technical services in the Maldives. Reliable solutions for homes and businesses.
                </p>
                <!-- Social links -->
                <div class="flex space-x-2.5">
                    <a href="https://www.facebook.com/micronetmaldives" aria-label="Facebook" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 hover:bg-blue-600 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/micronetmv/" aria-label="Instagram" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 hover:bg-pink-600 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
                    </a>
                    <a href="https://x.com/micromotoMV" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 hover:bg-gray-600 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.733-8.835L1.254 2.25H8.08l4.259 5.631 5.905-5.631zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://wa.me/9609996210" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 hover:bg-green-600 rounded-xl flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="#" aria-label="LinkedIn — Coming Soon" title="LinkedIn — Coming Soon" class="w-9 h-9 bg-gray-800 hover:bg-blue-700 rounded-xl flex items-center justify-center transition-colors opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-white font-bold text-xs uppercase tracking-wider mb-5">Our Services</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#services" class="hover:text-red-400 transition-colors">CCTV Installation</a></li>
                    <li><a href="#services" class="hover:text-red-400 transition-colors">Networking & IT Support</a></li>
                    <li><a href="#services" class="hover:text-red-400 transition-colors">Web Development</a></li>
                    <li><a href="#services" class="hover:text-red-400 transition-colors">Computer Repair</a></li>
                    <li><a href="#services" class="hover:text-red-400 transition-colors">Air Conditioning</a></li>
                    <li><a href="#services" class="hover:text-red-400 transition-colors">Motorcycle Repair</a></li>
                    <li><a href="#easyfix" class="hover:text-red-400 transition-colors">EasyFix</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-white font-bold text-xs uppercase tracking-wider mb-5">Contact</h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:9996210" class="hover:text-red-400 transition-colors">9996210</a>
                    </li>
                    <li class="flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:hi@micronet.mv" class="hover:text-red-400 transition-colors">hi@micronet.mv</a>
                    </li>
                    <li class="flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>M. Ithaamuiyge 1, 10th Floor, Alimasmagu</span>
                    </li>
                    <li class="flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        <span>micronet.mv</span>
                    </li>
                </ul>
            </div>

        </div>

        <!-- ── Our Brands ── -->
        <div class="mt-12 pt-8 border-t border-gray-800/60">
            <p class="text-xs text-gray-600 uppercase tracking-widest font-bold mb-4">Our Brands</p>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">

                <a href="https://micromoto.mv" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center border border-gray-800 hover:border-red-700 hover:bg-red-700/10 text-gray-400 hover:text-red-400 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Micro Moto Garage
                    <svg class="w-3 h-3 ml-1.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <a href="https://microcool.mv" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center border border-gray-800 hover:border-blue-700 hover:bg-blue-700/10 text-gray-400 hover:text-blue-400 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707M18.36 18.36l.707.707M1 12h2M21 12h2M4.22 19.78l.707-.707M18.36 5.64l.707-.707M12 7a5 5 0 110 10A5 5 0 0112 7z"/></svg>
                    Micro Cool
                    <svg class="w-3 h-3 ml-1.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>

                <a href="https://easyfix.mv" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center border border-gray-800 hover:border-red-600 hover:bg-red-600/10 text-gray-400 hover:text-red-400 text-sm font-semibold px-5 py-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    EasyFix
                    <svg class="w-3 h-3 ml-1.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
            <span>&copy; {{ date('Y') }} Micronet. All rights reserved. | micronet.mv</span>
            <div class="flex space-x-5">
                <a href="{{ route('privacy.policy') }}" class="hover:text-gray-400 transition-colors">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gray-400 transition-colors">Terms of Service</a>
            </div>
        </div>
    </div>

</footer>

</body>
</html>
