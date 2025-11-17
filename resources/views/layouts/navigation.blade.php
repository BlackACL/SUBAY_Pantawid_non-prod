<aside class="w-64 min-w-[16rem] max-w-[16rem] h-screen border-r border-gray-200 flex flex-col shadow-lg bg-cover bg-center" 
       style="background-image: url('/images/bg.png');">
    <!-- Logo -->
    <div class="flex items-center justify-center py-8 border-b">
        @if(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('superadmin'))
            <a href="{{ route('superadmin.dashboard') }}">
                <img src="/images/dswd_logoXI.png" alt="DSWD Logo" class="h-16 w-auto drop-shadow" />
            </a>
        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Regional DPSC'))
            <a href="{{ route('Regional.Dashboard') }}">
                <img src="/images/dswd_logoXI.png" alt="DSWD Logo" class="h-16 w-auto drop-shadow" />
            </a>
        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Provincial DPSC'))
            <a href="{{ route('Provincial.Dashboard') }}">
                <img src="/images/dswd_logoXI.png" alt="DSWD Logo" class="h-16 w-auto drop-shadow" />
            </a>
        @else
            <a href="{{ route('Inventory') }}">
                <img src="/images/dswd_logoXI.png" alt="DSWD Logo" class="h-16 w-auto drop-shadow" />
            </a>
        @endif
    </div>
    <!-- Navigation Links (vertical, enhanced, consistent) -->
    <nav class="flex-1 flex flex-col gap-1 px-4 py-4">
        @if(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('superadmin'))
            <x-nav-link :href="route('superadmin.dashboard')" :active="request()->routeIs('superadmin.dashboard')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('superadmin.dashboard') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-tachometer-alt text-white"></i>
                <span class="text-lg text-white">Dashboard</span>
            </x-nav-link>
            <x-nav-link :href="route('superadmin.logs_nav.logs')" :active="request()->routeIs('superadmin.logs_nav.logs')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('superadmin.logs_nav.logs') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-list-alt text-white"></i>
                <span class="text-lg text-white">Logs</span>
            </x-nav-link>
            <x-nav-link :href="route('users')" :active="request()->routeIs('users')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('users') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-users text-white"></i>
                <span class="text-lg text-white">Users</span>
            </x-nav-link>
            <x-nav-link :href="route('archives')" :active="request()->routeIs('archives')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('archives') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-archive text-white"></i>
                <span class="text-lg text-white">Archives</span>
            </x-nav-link>
            <x-nav-link :href="route('superadmin.inventory')" :active="request()->routeIs('superadmin.inventory')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('superadmin.inventory') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-boxes text-white"></i>
                <span class="text-lg text-white">Inventory</span>
            </x-nav-link>
            <x-nav-link :href="route('officials.index')" :active="request()->routeIs('officials.index')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('officials.index') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-cogs text-white"></i>
                <span class="text-lg text-white">System Management</span>
            </x-nav-link>
        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Regional DPSC'))
            <x-nav-link :href="route('Regional.Dashboard')" :active="request()->routeIs('Regional.Dashboard')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Regional.Dashboard') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-tachometer-alt text-white"></i>
                <span class="text-lg text-white">Dashboard</span>
            </x-nav-link>
            <x-nav-link :href="route('Regional.VerifiedFETS')" :active="request()->routeIs('Regional.VerifiedFETS')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Regional.VerifiedFETS') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-check-circle text-white"></i>
                <span class="text-lg text-white">Verified FETS</span>
            </x-nav-link>
            <x-nav-link :href="route('Regional.ApprovedFETS')" :active="request()->routeIs('Regional.ApprovedFETS')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Regional.ApprovedFETS') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-thumbs-up text-white"></i>
                <span class="text-lg text-white">Approved FETS</span>
            </x-nav-link>
            <x-nav-link :href="route('Regional.MyInventory')" :active="request()->routeIs('Regional.MyInventory')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Regional.MyInventory') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-boxes text-white"></i>
                <span class="text-lg text-white">Inventory</span>
            </x-nav-link>
            <x-nav-link :href="route('fets.submittedEmbed')" :active="request()->routeIs('fets.submittedEmbed')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('fets.submittedEmbed') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-paper-plane text-white"></i>
                <span class="text-lg text-white">Submitted FETS Request</span>
            </x-nav-link>
            <x-nav-link :href="route('inventory.upload')" :active="false" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5]">
                <i class="fas fa-file-export text-white"></i>
                <span class="text-lg text-white">Inventory Management</span>
            </x-nav-link>
        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Provincial DPSC'))
            <x-nav-link :href="route('Provincial.Dashboard')" :active="request()->routeIs('Provincial.Dashboard')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Provincial.Dashboard') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-tachometer-alt text-white"></i>
                <span class="text-lg text-white">Dashboard</span>
            </x-nav-link>
            <x-nav-link :href="route('Provincial.FETSrequest')" :active="request()->routeIs('Provincial.FETSrequest')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Provincial.FETSrequest') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-file-alt text-white"></i>
                <span class="text-lg text-white">FETS Request</span>
            </x-nav-link>
            <x-nav-link :href="route('Provincial.VerifiedFETS')" :active="request()->routeIs('Provincial.VerifiedFETS')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Provincial.VerifiedFETS') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-check-circle text-white"></i>
                <span class="text-lg text-white">Verified FETS</span>
            </x-nav-link>
            <x-nav-link :href="route('Provincial.MyInventory')" :active="request()->routeIs('Provincial.MyInventory')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Provincial.MyInventory') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-boxes text-white"></i>
                <span class="text-lg text-white">Inventory</span>
            </x-nav-link>
            <x-nav-link :href="route('fets.submittedEmbed')" :active="request()->routeIs('fets.submittedEmbed')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('fets.submittedEmbed') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-paper-plane text-white"></i>
                <span class="text-lg text-white">Submitted FETS Request</span>
            </x-nav-link>
            <x-nav-link :href="route('Provincial.ReturnFromRepair')" :active="request()->routeIs('Provincial.ReturnFromRepair')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Provincial.ReturnFromRepair') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-tools text-white"></i>
                <span class="text-lg text-white">Return from Repair</span>
            </x-nav-link>
        @else
            <x-nav-link :href="route('Inventory')" :active="request()->routeIs('Inventory')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('Inventory') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-box text-white"></i>
                <span class="text-lg text-white">My Inventory</span>
            </x-nav-link>
            
            @php
                // Check if user is Head of Property
                $isHeadOfProperty = \App\Models\Official::where('role', 'Head of Property')
                    ->where('active', true)
                    ->where('user_id', auth()->id())
                    ->exists();
            @endphp
            
            @if($isHeadOfProperty)
                <x-nav-link :href="route('unserviceable.units')" :active="request()->routeIs('unserviceable.units')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('unserviceable.units') ? 'bg-[#6176a3] font-bold' : '' }}">
                    <i class="fas fa-exclamation-triangle text-white"></i>
                    <span class="text-lg text-white">Unserviceable Units</span>
                </x-nav-link>
            @endif
            
            <x-nav-link :href="route('fets.select')" :active="request()->routeIs('FETS')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('FETS') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-file-alt text-white"></i>
                <span class="text-lg text-white">FETS</span>
            </x-nav-link>
            <x-nav-link :href="route('SubmittedFETS')" :active="request()->routeIs('SubmittedFETS')" class="block w-full text-left px-4 py-2 rounded-lg transition flex items-center gap-2 hover:bg-[#74a7b5] {{ request()->routeIs('SubmittedFETS') ? 'bg-[#6176a3] font-bold' : '' }}">
                <i class="fas fa-paper-plane text-white"></i>
                <span class="text-lg text-white">Submitted FETS</span>
            </x-nav-link>
        @endif
    </nav>

    <!-- Manual Button -->
    <div class="px-4 pb-3">
        <a href="{{ route('manual.view') }}" target="_blank" 
           class="block w-full text-center px-4 py-3 rounded-lg bg-yellow-500 hover:bg-yellow-600 transition text-white font-semibold shadow-lg">
            <i class="fas fa-book mr-2"></i>
            <span class="text-base">User Manual</span>
        </a>
    </div>

    <!-- Account Section (moved below links, aligned, no icons for email/role) -->
    <div class="border-t border-white border-opacity-20 px-3.5 py-3 flex-shrink-0">
        <div class="flex items-center justify-between gap-2">
            <div class="flex flex-col min-w-0">
                <div class="font-medium text-base text-white break-words leading-tight">
                    <i class="fas fa-user-circle text-xl text-white mr-1"></i>
                    <span class="whitespace-normal break-words">
                        {{ optional(Auth::user())->fullname ?: optional(Auth::user())->name ?: (optional(Auth::user())->first_name ? optional(Auth::user())->first_name . ' ' . optional(Auth::user())->last_name : optional(Auth::user())->email) }}
                    </span>
                </div>
                <div class="font-medium text-sm text-white mt-1 break-all whitespace-normal">
                    {{ optional(Auth::user())->email ?? '' }}
                </div>
                <div class="font-medium text-xs text-white mt-1">
                        @if(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('superadmin'))
                            Superadmin
                        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Regional DPSC'))
                            Regional DPSC
                        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Provincial DPSC'))
                            Provincial DPSC – {{ data_get(Auth::user(), 'province', '') }}
                        @elseif(Auth::check() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('Employee'))
                            Employee – {{ data_get(Auth::user(), 'province', '') }}
                        @else
                            User
                        @endif
                    </div>
            </div>
            <div class="flex-shrink-0 ml-2">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                    <div @click="open = ! open">
                        <button class="text-white hover:bg-[#6176a3] focus:outline-none transition flex items-center"
                            title="Account Options">
                            <i class="fas fa-cog text-xl"></i>
                        </button>
                    </div>

                    <div x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 bottom-full mb-2 w-48 rounded-md shadow-lg ltr:origin-bottom-right rtl:origin-bottom-left end-0"
                            style="display: none;"
                            @click="open = false">
                        <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                            <x-dropdown-link :href="route('password.edit')">
                                <i class="fas fa-key mr-2"></i>
                                {{ __('Update Password') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>