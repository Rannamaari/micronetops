<nav x-data="{ open: false }" class="bg-white/95 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ Auth::user()->canAccessOperations() ? route('dashboard') : route('rattehin.index') }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <x-application-logo class="h-8 w-auto" />
                </a>
            </div>

            <!-- Desktop & Tablet Navigation -->
            <div class="hidden md:flex items-center gap-1">
                @if(Auth::user()->canAccessHR())
                    <a href="{{ route('hr.dashboard') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('hr.*') || request()->routeIs('employees.*') || request()->routeIs('loans.*') || request()->routeIs('payroll.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                        HR
                    </a>
                @endif

                @if(Auth::user()->canAccessOperations())
                    <a href="{{ route('dashboard') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                        Dashboard
                    </a>

                    @if(Auth::user()->canViewCustomers())
                        <a href="{{ route('customers.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Customers
                        </a>
                    @endif

                    @if(Auth::user()->canCreateJobs())
                        <a href="{{ route('jobs.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('jobs.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Jobs
                        </a>
                    @endif

                    <a href="{{ route('reports.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                        Reports
                    </a>

                    @if(Auth::user()->canCreateExpenses())
                        <a href="{{ route('petty-cash.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Petty Cash
                        </a>
                    @elseif(Auth::user()->isCashier())
                        <a href="{{ route('petty-cash.history') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.history') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Expenses
                        </a>
                    @endif

                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <a href="{{ route('inventory.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('inventory.*') || request()->routeIs('inventory-categories.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Inventory
                        </a>
                    @endif

                    @if(Auth::user()->canManageUsers())
                        <a href="{{ route('users.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Users
                        </a>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('roles.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('roles.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Roles
                        </a>
                    @endif
                @endif

                <a href="{{ route('rattehin.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('rattehin.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                    Rattehin
                </a>
            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-3">
                <!-- User Menu -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200">
                        <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                        <span class="sm:hidden">{{ Str::limit(Auth::user()->name, 10, '') }}</span>
                        @if(Auth::user()->isPremium())
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-sm">
                                {{ Auth::user()->isPremium() ? 'PRO' : 'PREMIUM' }}
                            </span>
                        @endif
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 overflow-hidden">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-all duration-200 active:scale-95">
                    <svg class="w-6 h-6" :class="{'hidden': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="w-6 h-6" :class="{'hidden': !open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-4 space-y-1 max-h-[calc(100vh-5rem)] overflow-y-auto">
            @if(Auth::user()->canAccessHR())
                <a href="{{ route('hr.dashboard') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('hr.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">HR Dashboard</a>
            @endif

            @if(Auth::user()->canAccessOperations())
                <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Dashboard</a>

                @if(Auth::user()->canViewCustomers())
                    <a href="{{ route('customers.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Customers</a>
                @endif

                @if(Auth::user()->canCreateJobs())
                    <a href="{{ route('jobs.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('jobs.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Jobs</a>
                @endif

                <!-- Reports Accordion -->
                <div x-data="{ reportsOpen: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                    <button @click="reportsOpen = !reportsOpen" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">
                        <span>Reports</span>
                        <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="reportsOpen"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="mt-2 ml-3 space-y-1 pl-3 border-l-2 border-gray-200">
                        <a href="{{ route('reports.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Overview</a>
                        <a href="{{ route('reports.road-worthiness') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.road-worthiness') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Road Worthiness</a>
                        <a href="{{ route('reports.daily-sales') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.daily-sales') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Daily Sales</a>
                        <a href="{{ route('reports.best-sellers') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.best-sellers') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Best Sellers</a>
                        <a href="{{ route('reports.low-inventory') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.low-inventory') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Low Inventory</a>
                        <a href="{{ route('reports.sales-trends') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.sales-trends') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Sales Trends</a>
                        <a href="{{ route('reports.inventory-overview') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.inventory-overview') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Inventory Overview</a>
                    </div>
                </div>

                @if(Auth::user()->canCreateExpenses())
                    <a href="{{ route('petty-cash.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Petty Cash</a>
                @elseif(Auth::user()->isCashier())
                    <a href="{{ route('petty-cash.history') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.history') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Expenses</a>
                @endif

                @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                    <a href="{{ route('inventory.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('inventory.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Inventory</a>
                @endif

                @if(Auth::user()->canManageUsers())
                    <a href="{{ route('users.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Users</a>
                @endif

                @if(Auth::user()->isAdmin())
                    <a href="{{ route('roles.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('roles.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Roles</a>
                @endif
            @endif

            <a href="{{ route('rattehin.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('rattehin.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Rattehin</a>
        </div>
    </div>

    <!-- Reports Sub Navigation (Desktop/Tablet Only) -->
    @if(request()->routeIs('reports.*'))
        <div class="hidden md:block border-t border-gray-100 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 lg:gap-8 overflow-x-auto py-3 scrollbar-hide">
                    <a href="{{ route('reports.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.index') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Overview
                    </a>
                    <a href="{{ route('reports.road-worthiness') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.road-worthiness') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Road Worthiness
                    </a>
                    <a href="{{ route('reports.daily-sales') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.daily-sales') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Daily Sales
                    </a>
                    <a href="{{ route('reports.best-sellers') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.best-sellers') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Best Sellers
                    </a>
                    <a href="{{ route('reports.low-inventory') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.low-inventory') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Low Inventory
                    </a>
                    <a href="{{ route('reports.sales-trends') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.sales-trends') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Sales Trends
                    </a>
                    <a href="{{ route('reports.inventory-overview') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.inventory-overview') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Inventory Overview
                    </a>
                </div>
            </div>
        </div>
    @endif
</nav>

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
