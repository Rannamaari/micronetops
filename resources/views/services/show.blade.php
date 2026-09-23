<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $service['name'] }} | Micronet Maldives</title>
    <meta name="description" content="{{ $service['short'] }} Talk to Micronet about your requirements.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Manrope','sans-serif'],display:['Space Grotesk','sans-serif']}}}}</script>
    <style>
        .hero-grid { background-color:#08090c; background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px),radial-gradient(circle at 85% 10%,rgba(220,38,38,.24),transparent 30%); background-size:42px 42px,42px 42px,auto; }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-900 font-sans">
    <header class="bg-zinc-950 text-white sticky top-0 z-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-18 min-h-[72px] flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center"><img src="{{ asset('logo.png') }}" alt="Micronet" class="h-10 w-auto brightness-0 invert"></a>
            <nav class="flex items-center gap-3 sm:gap-6 text-sm font-bold"><a href="{{ route('home') }}#services" class="text-zinc-300 hover:text-white">All services</a><a href="#enquiry" class="bg-red-600 hover:bg-red-500 px-4 py-2.5 rounded-xl text-white">Discuss your project</a></nav>
        </div>
    </header>

    <main>
        <section class="hero-grid text-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 lg:py-28 grid lg:grid-cols-[1.3fr_.7fr] gap-12 items-center">
                <div>
                    <a href="{{ route('home') }}#services" class="inline-flex items-center gap-2 text-sm font-bold text-red-300 hover:text-white mb-7">&larr; All Micronet services</a>
                    <p class="text-red-400 text-xs font-extrabold tracking-[.2em] uppercase mb-4">Micronet digital solutions</p>
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.06]">{{ $service['name'] }}</h1>
                    <p class="text-zinc-300 text-lg sm:text-xl leading-relaxed max-w-2xl mt-6">{{ $service['description'] }}</p>
                    <a href="#enquiry" class="inline-flex mt-9 bg-red-600 hover:bg-red-500 px-7 py-4 rounded-xl font-extrabold shadow-xl shadow-red-950/30">Tell us your requirements</a>
                </div>
                <div class="border border-red-500/25 bg-red-500/10 rounded-[2rem] p-8 sm:p-10">
                    <div class="w-16 h-16 rounded-2xl bg-red-600 flex items-center justify-center mb-8"><svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg></div>
                    <p class="text-zinc-400 text-sm font-bold uppercase tracking-wider">What we can deliver</p>
                    <ul class="mt-5 space-y-4">@foreach ($service['deliverables'] as $deliverable)<li class="flex gap-3 text-zinc-100"><span class="text-red-400 font-black">+</span>{{ $deliverable }}</li>@endforeach</ul>
                </div>
            </div>
        </section>

        <section class="py-18 lg:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="max-w-3xl"><p class="text-red-600 text-xs font-extrabold tracking-[.18em] uppercase">Built for real operations</p><h2 class="font-display text-3xl sm:text-4xl font-bold mt-3">We build around the way your business works.</h2><p class="text-zinc-600 leading-relaxed mt-5 text-lg">No two businesses have the same workflow. We start by listening, define what needs to happen, and create a solution your staff and customers can actually use.</p></div>
                <div class="grid md:grid-cols-3 gap-5 mt-10">
                    <article class="rounded-2xl bg-zinc-100 p-6"><p class="text-red-600 font-black text-sm">01</p><h3 class="font-bold text-lg mt-4">Share your idea</h3><p class="text-zinc-600 mt-2 text-sm">Tell us the problem, goal, or workflow you want to improve.</p></article>
                    <article class="rounded-2xl bg-zinc-100 p-6"><p class="text-red-600 font-black text-sm">02</p><h3 class="font-bold text-lg mt-4">Plan the system</h3><p class="text-zinc-600 mt-2 text-sm">We turn the requirement into a practical scope and clear next steps.</p></article>
                    <article class="rounded-2xl bg-zinc-100 p-6"><p class="text-red-600 font-black text-sm">03</p><h3 class="font-bold text-lg mt-4">Build and support</h3><p class="text-zinc-600 mt-2 text-sm">We develop, launch, train your team, and stay available as you grow.</p></article>
                </div>
            </div>
        </section>

        <section class="py-18 lg:py-24 bg-zinc-950 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6"><p class="text-red-400 text-xs font-extrabold tracking-[.18em] uppercase">Selected work</p><h2 class="font-display text-3xl sm:text-4xl font-bold mt-3">Systems made for Maldivian businesses.</h2><div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4 mt-10"><article class="border border-white/10 rounded-2xl p-6"><p class="text-red-400 text-xs font-bold uppercase">Operations platform</p><h3 class="font-bold text-xl mt-3">EasyFix</h3><p class="text-zinc-400 text-sm mt-3">A service platform supporting handyman requests and operations in Greater Mal&eacute;.</p></article><article class="border border-white/10 rounded-2xl p-6"><p class="text-red-400 text-xs font-bold uppercase">Retail POS</p><h3 class="font-bold text-xl mt-3">MicroPOS</h3><p class="text-zinc-400 text-sm mt-3">Used by Island Thrift in Himmafushi for day-to-day retail operations.</p></article><article class="border border-white/10 rounded-2xl p-6"><p class="text-red-400 text-xs font-bold uppercase">Retail operations</p><h3 class="font-bold text-xl mt-3">Moscow Traders</h3><p class="text-zinc-400 text-sm mt-3">A system tailored for Moscow Traders, a major shopping centre in Himmafushi.</p></article><article class="border border-white/10 rounded-2xl p-6"><p class="text-red-400 text-xs font-bold uppercase">Booking service</p><h3 class="font-bold text-xl mt-3">Himmafushi.net</h3><p class="text-zinc-400 text-sm mt-3">An online booking experience made for visitors and local service discovery.</p></article></div></div>
        </section>

        <section id="enquiry" class="py-18 lg:py-24 bg-red-600">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 grid lg:grid-cols-[.8fr_1.2fr] gap-10 items-start">
                <div class="text-white">
                    <p class="text-red-100 text-xs font-extrabold tracking-[.18em] uppercase">Start a conversation</p>
                    <h2 class="font-display text-4xl sm:text-5xl font-bold leading-tight mt-4">Let&rsquo;s bring your idea to life.</h2>
                    <p class="text-red-100 text-lg leading-relaxed mt-5">Describe what you need. Whether it is a website, an app, a POS system, or a better internal process, we will get back to you with the right next step.</p>
                    <p class="mt-8 font-bold">Or call us on <a class="underline" href="tel:9996210">9996210</a>.</p>
                </div>
                <form method="POST" action="{{ route('contact.store') }}" class="bg-white rounded-3xl p-6 sm:p-8 text-zinc-900 shadow-2xl">
                    @csrf
                    <input type="hidden" name="service" value="{{ $service['name'] }}">
                    @if (session('success'))
                        <div class="mb-5 rounded-xl bg-green-50 text-green-800 p-4 text-sm font-semibold">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="mb-5 rounded-xl bg-red-50 text-red-800 p-4 text-sm font-semibold">{{ session('error') }}</div>
                    @endif
                    <div class="grid sm:grid-cols-2 gap-4">
                        <label class="text-sm font-bold">Your name<input required name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-xl border-zinc-300 focus:border-red-500 focus:ring-red-500" placeholder="Full name"></label>
                        <label class="text-sm font-bold">Company name<input name="company_name" value="{{ old('company_name') }}" class="mt-2 w-full rounded-xl border-zinc-300 focus:border-red-500 focus:ring-red-500" placeholder="Business or organisation"></label>
                        <label class="text-sm font-bold">Phone number<input required name="phone" value="{{ old('phone') }}" class="mt-2 w-full rounded-xl border-zinc-300 focus:border-red-500 focus:ring-red-500" placeholder="e.g. 9996210"></label>
                        <label class="text-sm font-bold">Email <span class="font-normal text-zinc-400">optional</span><input type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded-xl border-zinc-300 focus:border-red-500 focus:ring-red-500" placeholder="you@company.com"></label>
                    </div>
                    <label class="block text-sm font-bold mt-4">What do you need?<textarea required name="message" rows="5" class="mt-2 w-full rounded-xl border-zinc-300 focus:border-red-500 focus:ring-red-500" placeholder="Tell us about your idea, business process, or project requirements...">{{ old('message') }}</textarea></label>
                    <button class="mt-5 w-full bg-zinc-950 hover:bg-zinc-800 text-white font-extrabold py-4 rounded-xl">Send project enquiry</button>
                </form>
            </div>
        </section>
    </main>
    <footer class="bg-zinc-950 text-zinc-500 py-7 text-center text-sm"><a href="{{ route('home') }}" class="hover:text-white">Micronet</a> &middot; Websites, apps, systems, and IT solutions for the Maldives</footer>
</body>
</html>
