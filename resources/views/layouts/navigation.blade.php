<nav x-data="{ open: false }" class="bg-white/95 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ Auth::user()->canAccessOperations() ? route('dashboard') : route('rattehin.index') }}" class="flex items-center hover:opacity-80 transition-opacity">
                    <x-application-logo class="h-8 w-auto" />
                </a>
            </div>

            @php
                $isSalesSection = request()->routeIs('customers.*')
                    || request()->routeIs('leads.*')
                    || request()->routeIs('jobs.*')
                    || request()->routeIs('sales.*')
                    || request()->routeIs('inventory.*')
                    || request()->routeIs('inventory-categories.*');
            @endphp

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

                    @if(Auth::user()->canCreateJobs())
                        <a href="{{ route('sales.daily.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $isSalesSection ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Sales
                        </a>
                    @endif

                    @if(Auth::user()->canViewReports())
                        <a href="{{ route('reports.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Reports
                        </a>
                    @endif

                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <a href="{{ route('expenses.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') || request()->routeIs('vendors.*') || request()->routeIs('accounts.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            Expenses
                        </a>
                    @endif

                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <a href="{{ route('sms.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('sms.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                            SMS
                        </a>
                    @endif

                    <a href="{{ route('faults.index') }}" class="px-3 lg:px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('faults.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
                        Faults
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

                @if(Auth::user()->canCreateJobs())
                    <!-- Sales Accordion -->
                    <div x-data="{ salesOpen: {{ $isSalesSection ? 'true' : 'false' }} }">
                        <button @click="salesOpen = !salesOpen" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ $isSalesSection ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">
                            <span>Sales</span>
                            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': salesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="salesOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="mt-2 ml-3 space-y-1 pl-3 border-l-2 border-gray-200">
                            <a href="{{ route('customers.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Customers</a>
                            <a href="{{ route('leads.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('leads.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Leads</a>
                            <a href="{{ route('jobs.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('jobs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Jobs</a>
                            @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                                <a href="{{ route('inventory.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('inventory.*') || request()->routeIs('inventory-categories.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Inventory</a>
                            @endif
                            <a href="{{ route('sales.daily.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('sales.daily.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Daily Sales</a>
                            @if(Auth::user()->canViewReports())
                                <a href="{{ route('sales.reports') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('sales.reports') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Sales Reports</a>
                            @endif
                            @if(Auth::user()->canRunEod())
                                <a href="{{ route('sales.eod.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('sales.eod.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">End of Day</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if(Auth::user()->canViewReports())
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
                            @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                                <a href="{{ route('reports.pnl') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('reports.pnl') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Profit & Loss</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                    <!-- Expenses Accordion -->
                    <div x-data="{ expensesOpen: {{ (request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') || request()->routeIs('vendors.*') || request()->routeIs('accounts.*') || request()->routeIs('recurring-expenses.*')) ? 'true' : 'false' }} }">
                        <button @click="expensesOpen = !expensesOpen" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ (request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') || request()->routeIs('vendors.*') || request()->routeIs('accounts.*') || request()->routeIs('recurring-expenses.*')) ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">
                            <span>Expenses</span>
                            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': expensesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="expensesOpen"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="mt-2 ml-3 space-y-1 pl-3 border-l-2 border-gray-200">
                            <a href="{{ route('expenses.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('expenses.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Expenses</a>
                            <a href="{{ route('expense-categories.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('expense-categories.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Expense Categories</a>
                            <a href="{{ route('vendors.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('vendors.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Vendors</a>
                            <a href="{{ route('accounts.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('accounts.index') || request()->routeIs('accounts.show') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Accounts</a>
                            <a href="{{ route('accounts.logs') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('accounts.logs') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Account Logs</a>
                            <a href="{{ route('recurring-expenses.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('recurring-expenses.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Recurring Expenses</a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('activity-log.index') }}" class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('activity-log.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 active:bg-gray-50' }}">Activity Log</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                    <a href="{{ route('sms.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('sms.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">SMS</a>
                @endif

                @if(Auth::user()->canCreateExpenses())
                    <a href="{{ route('petty-cash.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Petty Cash</a>
                @elseif(Auth::user()->isCashier())
                    <a href="{{ route('petty-cash.history') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.history') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Expenses</a>
                @endif

                <a href="{{ route('faults.index') }}" class="block px-4 py-3 rounded-xl text-base font-medium transition-all duration-200 {{ request()->routeIs('faults.*') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 active:bg-gray-100' }}">Faults</a>

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

    <!-- Sales Sub Navigation (Desktop/Tablet Only) -->
    @if($isSalesSection)
        <div class="hidden md:block border-t border-gray-100 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 lg:gap-8 overflow-x-auto py-3 scrollbar-hide">
                    <a href="{{ route('customers.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('customers.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Customers
                    </a>
                    <a href="{{ route('leads.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('leads.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Leads
                    </a>
                    <a href="{{ route('jobs.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('jobs.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Jobs
                    </a>
                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <a href="{{ route('inventory.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('inventory.*') || request()->routeIs('inventory-categories.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                            Inventory
                        </a>
                    @endif
                    <a href="{{ route('sales.daily.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('sales.daily.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Daily Sales
                    </a>
                    @if(Auth::user()->canViewReports())
                        <a href="{{ route('sales.reports') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('sales.reports') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                            Sales Reports
                        </a>
                    @endif
                    @if(Auth::user()->canRunEod())
                        <a href="{{ route('sales.eod.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('sales.eod.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                            End of Day
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Expenses Sub Navigation (Desktop/Tablet Only) -->
    @if(request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') || request()->routeIs('vendors.*') || request()->routeIs('accounts.*') || request()->routeIs('recurring-expenses.*'))
        <div class="hidden md:block border-t border-gray-100 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6 lg:gap-8 overflow-x-auto py-3 scrollbar-hide">
                    <a href="{{ route('expenses.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('expenses.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Expenses
                    </a>
                    <a href="{{ route('expense-categories.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('expense-categories.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Expense Categories
                    </a>
                    <a href="{{ route('vendors.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('vendors.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Vendors
                    </a>
                    <a href="{{ route('accounts.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('accounts.index') || request()->routeIs('accounts.show') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Accounts
                    </a>
                    <a href="{{ route('accounts.logs') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('accounts.logs') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Account Logs
                    </a>
                    <a href="{{ route('recurring-expenses.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('recurring-expenses.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                        Recurring Expenses
                    </a>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('activity-log.index') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('activity-log.*') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                            Activity Log
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

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
                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <a href="{{ route('reports.pnl') }}" class="text-sm lg:text-base whitespace-nowrap transition-all duration-200 py-2 {{ request()->routeIs('reports.pnl') ? 'font-semibold text-gray-900 border-b-2 border-gray-900' : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300' }}">
                            Profit & Loss
                        </a>
                    @endif
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
