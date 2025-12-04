<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Dashboard - All users --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Customers - Admin, Manager, Mechanic (not Cashier) --}}
                    @if(Auth::user()->canViewCustomers())
                        <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                            {{ __('Customers') }}
                        </x-nav-link>
                    @endif

                    {{-- Jobs - Admin, Manager, Mechanic (not Cashier) --}}
                    @if(Auth::user()->canCreateJobs())
                        <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                            {{ __('Jobs') }}
                        </x-nav-link>
                    @endif

                    {{-- Reports - All users --}}
                    <x-nav-link :href="route('reports.road-worthiness')" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-nav-link>

                    {{-- Petty Cash - Admin, Manager, Mechanic (not Cashier) --}}
                    @if(Auth::user()->canCreateExpenses())
                        <x-nav-link :href="route('petty-cash.index')" :active="request()->routeIs('petty-cash.*')">
                            {{ __('Petty Cash') }}
                        </x-nav-link>
                    @elseif(Auth::user()->isCashier())
                        {{-- Cashiers can view expense history --}}
                        <x-nav-link :href="route('petty-cash.history')" :active="request()->routeIs('petty-cash.history')">
                            {{ __('Expense History') }}
                        </x-nav-link>
                    @endif

                    {{-- Inventory - Admin, Manager only --}}
                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <x-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*') || request()->routeIs('inventory-categories.*')">
                            {{ __('Inventory') }}
                        </x-nav-link>
                    @endif

                    {{-- Users - Admin, Manager --}}
                    @if(Auth::user()->canManageUsers())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('Users') }}
                        </x-nav-link>
                    @endif

                    {{-- Roles - Admin only --}}
                    @if(Auth::user()->isAdmin())
                        <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                            {{ __('Roles') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.show')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        {{-- System Settings - Admin only --}}
                        @if(Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('system.settings')">
                                {{ __('System Settings') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            {{-- Dashboard - All users --}}
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- Customers - Admin, Manager, Mechanic (not Cashier) --}}
            @if(Auth::user()->canViewCustomers())
                <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                    {{ __('Customers') }}
                </x-responsive-nav-link>
            @endif

            {{-- Jobs - Admin, Manager, Mechanic (not Cashier) --}}
            @if(Auth::user()->canCreateJobs())
                <x-responsive-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                    {{ __('Jobs') }}
                </x-responsive-nav-link>
            @endif

            {{-- Reports - All users --}}
            <x-responsive-nav-link :href="route('reports.road-worthiness')" :active="request()->routeIs('reports.*')">
                {{ __('Reports') }}
            </x-responsive-nav-link>

            {{-- Petty Cash - Admin, Manager, Mechanic (not Cashier) --}}
            @if(Auth::user()->canCreateExpenses())
                <x-responsive-nav-link :href="route('petty-cash.index')" :active="request()->routeIs('petty-cash.*')">
                    {{ __('Petty Cash') }}
                </x-responsive-nav-link>
            @elseif(Auth::user()->isCashier())
                {{-- Cashiers can view expense history --}}
                <x-responsive-nav-link :href="route('petty-cash.history')" :active="request()->routeIs('petty-cash.history')">
                    {{ __('Expense History') }}
                </x-responsive-nav-link>
            @endif

            {{-- Inventory - Admin, Manager only --}}
            @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*') || request()->routeIs('inventory-categories.*')">
                    {{ __('Inventory') }}
                </x-responsive-nav-link>
            @endif

            {{-- Users - Admin, Manager --}}
            @if(Auth::user()->canManageUsers())
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
            @endif

            {{-- Roles - Admin only --}}
            @if(Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                    {{ __('Roles') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                {{-- System Settings - Admin only --}}
                @if(Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('system.settings')">
                        {{ __('System Settings') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
