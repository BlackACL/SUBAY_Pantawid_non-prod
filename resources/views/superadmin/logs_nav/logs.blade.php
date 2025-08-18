<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight tracking-wide">
            {{ __('ACTIVITY LOGS') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#F6F8FA] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-gray-900">
                <div class="relative overflow-x-auto">
                    <form method="GET" action="{{ route('superadmin.logs_nav.logs') }}" id="logs-filter-form"
                          class="flex flex-wrap items-center justify-between mb-6 bg-white p-4 rounded-xl shadow-lg
                                 gap-4 md:gap-0 md:flex-row flex-col">
                        <div class="flex items-center gap-0 flex-wrap w-full md:w-auto">
                            <div class="relative w-full md:w-72">
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search user/email/IP address..."
                                    id="search-input"
                                    autocomplete="off"
                                    class="border-2 border-[#274C77] rounded-full pl-10 pr-10 py-2 text-sm text-left focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full bg-[#F8FAFC] shadow placeholder-gray-400"
                                    
                                >
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#274C77] pointer-events-none text-base">
                                    <i class="fas fa-search"></i>
                                </span>
                                <span id="search-loading" class="absolute right-10 top-1/2 transform -translate-y-1/2 text-[#274C77] text-base hidden">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                <button type="button" id="search-clear-btn"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 focus:outline-none"
                                    style="background:transparent; border:none; padding:0; margin:0;"
                                    title="Clear search">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap w-full md:w-auto mt-4 md:mt-0">
                            <!-- Group filters and clear button in a row, vertically centered -->
                            <div class="flex flex-row items-end gap-2 flex-wrap">
                                <div class="flex flex-col">
                                    <label for="date-range-filter" class="text-xs font-semibold text-[#274C77] mb-1">Date Range</label>
                                    <select name="date_range" id="date-range-filter"
                                        class="border-2 border-[#274C77] rounded-full px-6 py-2 text-xs focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full md:w-auto bg-white shadow appearance-none pr-10 font-medium text-[#274C77] hover:bg-[#F1F5F9]">
                                        <option value="">All Dates</option>
                                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today
                                        </option>
                                        <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>
                                            Yesterday
                                        </option>
                                        <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>
                                            Last 7 Days
                                        </option>
                                        <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>
                                            Last 30 Days
                                        </option>
                                        <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>
                                            This Month
                                        </option>
                                        <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom
                                            Range
                                        </option>
                                    </select>
                                </div>
                                <div class="flex flex-col" id="custom-date-range"
                                     style="{{ request('date_range') == 'custom' ? '' : 'display:none;' }}">
                                    <label for="date-from-filter" class="text-xs font-semibold text-[#274C77] mb-1">From</label>
                                    <input
                                        type="date"
                                        name="date_from"
                                        id="date-from-filter"
                                        value="{{ request('date_from') }}"
                                        class="border-2 border-[#274C77] rounded-full px-4 py-2 text-xs focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full md:w-auto bg-white shadow font-medium text-[#274C77] hover:bg-[#F1F5F9]"
                                    >
                                </div>
                                <div class="flex flex-col" id="custom-date-range-to"
                                     style="{{ request('date_range') == 'custom' ? '' : 'display:none;' }}">
                                    <label for="date-to-filter" class="text-xs font-semibold text-[#274C77] mb-1">To</label>
                                    <input
                                        type="date"
                                        name="date_to"
                                        id="date-to-filter"
                                        value="{{ request('date_to') }}"
                                        class="border-2 border-[#274C77] rounded-full px-4 py-2 text-xs focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full md:w-auto bg-white shadow font-medium text-[#274C77] hover:bg-[#F1F5F9]"
                                    >
                                </div>
                                <div class="flex flex-col">
                                    <label for="role-filter" class="text-xs font-semibold text-[#274C77] mb-1">Role</label>
                                    <div class="relative">
                                        <select name="role" id="role-filter"
                                            class="border-2 border-[#274C77] rounded-full px-6 py-2 text-xs focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full md:w-auto bg-white shadow appearance-none pr-10 font-medium text-[#274C77] hover:bg-[#F1F5F9]">
                                            <option value="">All Roles</option>
                                            <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Superadmin
                                            </option>
                                            <option value="Regional DPSC" {{ request('role') == 'Regional DPSC' ? 'selected' : '' }}>
                                                Regional DPSC
                                            </option>
                                            <option value="Provincial DPSC" {{ request('role') == 'Provincial DPSC' ? 'selected' : '' }}>
                                                Provincial DPSC
                                            </option>
                                            <option value="Employee" {{ request('role') == 'Employee' ? 'selected' : '' }}>Employee
                                            </option>
                                        </select>
                                        <span class="pointer-events-none absolute right-4 top-1/2 transform -translate-y-1/2 text-[#274C77] text-base">
                    
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col">
                                    <label for="activity-filter" class="text-xs font-semibold text-[#274C77] mb-1">Activity</label>
                                    <div class="relative">
                                        <select name="activity" id="activity-filter"
                                            class="border-2 border-[#274C77] rounded-full px-6 py-2 text-xs focus:ring-2 focus:ring-[#274C77] focus:outline-none transition w-full md:w-auto bg-white shadow appearance-none pr-10 font-medium text-[#274C77] hover:bg-[#F1F5F9]">
                                            <option value="">All Activities</option>
                                            @if(isset($allActivities))
                                                @php
                                                    $activityOptions = \Illuminate\Support\Collection::make($allActivities)->pluck('description')->unique()->filter()->sort()->values();
                                                @endphp
                                            @else
                                                @php
                                                    $activityOptions = \Illuminate\Support\Collection::make(
                                                        \Spatie\Activitylog\Models\Activity::orderBy('description')->get()
                                                    )->pluck('description')->unique()->filter()->sort()->values();
                                                @endphp
                                            @endif
                                            @foreach($activityOptions as $desc)
                                                <option value="{{ $desc }}" {{ request('activity') == $desc ? 'selected' : '' }}>{{ $desc }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute right-4 top-1/2 transform -translate-y-1/2 text-[#274C77] text-base">
                                           
                                        </span>
                                    </div>
                                </div>
                                <button type="button" id="clear-filters-btn"
                                    class="border border-[#274C77] rounded-full bg-white hover:bg-[#E3EAF3] text-[#274C77] shadow transition flex items-center gap-2 px-3 font-semibold text-xs mb-1"
                                    style="height:32px; min-width:0; align-self:end;"
                                    title="Clear all filters">
                                    <i class="fas fa-x text-base"></i>
                                    <span class="hidden md:inline">Clear Filters</span>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="logs-table-container" class="relative overflow-x-auto shadow-lg lg:rounded-lg bg-white">
                        <table class="min-w-full text-left rounded-lg overflow-hidden">
                            <thead class="text-xs font-semibold uppercase text-white bg-[#274C77]">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-[15%]">Date and Time</th>
                                <th scope="col" class="px-6 py-4 w-[30%]">User</th>
                                <th scope="col" class="px-6 py-4">Role</th>
                                <th scope="col" class="px-6 py-4">Ip Address</th>
                                <!-- <th scope="col" class="px-6 py-4 w-[15%]">Device</th> -->
                                <th scope="col" class="px-6 py-4">Activity</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($activities as $activity)
                                <tr class="border-b {{ $loop->even ? 'bg-[#F8F9FA]' : 'bg-white' }} text-black shadow-sm text-sm hover:bg-[#E3EAF3] transition">
                                    <td class="px-6 py-4 align-middle">
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($activity->created_at)->format('m-d-Y') }}</span><br>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="font-bold text-[#274C77]">{{ optional($activity->causer)->fullname ?? optional($activity->causer)->name ?? 'System' }}</span>
                                        @if(optional($activity->causer)->email)
                                            <br>
                                            <span class="text-xs text-gray-500">{{ optional($activity->causer)->email }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        @php
                                            $role = $activity->properties['role'] ?? 'N/A';
                                            // Lighter color coding for roles
                                            if ($role === 'superadmin') {
                                                $roleBg = 'bg-[#DBEAFE] text-[#1E3A8A]';
                                            } elseif ($role === 'Regional DPSC') {
                                                $roleBg = 'bg-[#D1FAE5] text-[#047857]';
                                            } elseif ($role === 'Provincial DPSC') {
                                                $roleBg = 'bg-[#FEF3C7] text-[#F59E0B]';
                                            } elseif ($role === 'Employee') {
                                                $roleBg = 'bg-[#F3F4F6] text-[#6B7280]';
                                            } else {
                                                $roleBg = 'bg-gray-100 text-gray-900';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full {{ $roleBg }} font-semibold text-xs shadow-sm border border-gray-100">
                                            {{ $role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $activity->properties['ip'] ?? 'N/A' }}</span>
                                    </td>
                                    <!-- <td class="px-6 py-4 align-middle">
                                        {{ $activity->properties['device'] ?? 'N/A' }}
                                    </td> -->
                                    <td class="px-6 py-4 align-middle">
                                        @php
                                            $activityText = $activity->description;
                                            $iconClass = 'far fa-file text-gray-300'; // Default subtle icon
                                            $lowerText = strtolower($activityText);

                                            if (str_contains($lowerText, 'login')) {
                                                $iconClass = 'fas fa-sign-in-alt text-green-400'; // Log in (arrow into box)
                                            } elseif (str_contains($lowerText, 'logout')) {
                                                $iconClass = 'fas fa-sign-out-alt text-red-400'; // Log out (arrow out of box)
                                            } elseif (str_contains($lowerText, 'added')) {
                                                $iconClass = 'far fa-user text-blue-300';
                                            } elseif (str_contains($lowerText, 'submitted')) {
                                                $iconClass = 'far fa-paper-plane text-indigo-300';
                                            } elseif (str_contains($lowerText, 'verified')) {
                                                $iconClass = 'far fa-check-circle text-green-300';
                                            } elseif (str_contains($lowerText, 'approved')) {
                                                $iconClass = 'far fa-thumbs-up text-yellow-300';
                                            } elseif (str_contains($lowerText, 'archived')) {
                                                $iconClass = 'far fa-folder text-gray-400';
                                            } elseif (str_contains($lowerText, 'rejected')) {
                                                $iconClass = 'far fa-circle-xmark text-red-300';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-2">
                                            <i class="{{ $iconClass }}"></i>
                                            <span class="text-gray-700">{{ $activityText }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">No activity logs found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div>
                            {{-- Pagination links --}}
                            <div class="mt-4 p-4 rounded-lg shadow bg-white flex justify-center">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function fetchLogs() {
            const form = document.getElementById('logs-filter-form');
            const container = document.getElementById('logs-table-container');
            const searchLoading = document.getElementById('search-loading');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            searchLoading && searchLoading.classList.remove('hidden');
            fetch(form.action + '?' + params, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                // Extract only the logs table and pagination from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.getElementById('logs-table-container');
                if (newTable && container) {
                    container.innerHTML = newTable.innerHTML;
                }
                searchLoading && searchLoading.classList.add('hidden');
            });
        }

        document.getElementById('role-filter').addEventListener('change', fetchLogs);
        document.getElementById('activity-filter').addEventListener('change', fetchLogs);
        document.getElementById('date-range-filter').addEventListener('change', function() {
            var customRange = this.value === 'custom';
            document.getElementById('custom-date-range').style.display = customRange ? '' : 'none';
            document.getElementById('custom-date-range-to').style.display = customRange ? '' : 'none';
            fetchLogs();
        });
        document.getElementById('search-input').addEventListener('input', function() {
            clearTimeout(window.liveSearchTimeout);
            document.getElementById('search-loading').classList.remove('hidden');
            window.liveSearchTimeout = setTimeout(fetchLogs, 400);
            document.getElementById('search-clear-btn').style.display = this.value ? 'inline' : 'none';
        });
        document.getElementById('search-clear-btn').addEventListener('click', function() {
            var input = document.getElementById('search-input');
            input.value = '';
            this.style.display = 'none';
            fetchLogs();
        });
        document.getElementById('clear-filters-btn').addEventListener('click', function() {
            document.getElementById('search-input').value = '';
            document.getElementById('search-clear-btn').style.display = 'none';
            document.getElementById('date-range-filter').selectedIndex = 0;
            document.getElementById('role-filter').selectedIndex = 0;
            document.getElementById('activity-filter').selectedIndex = 0;
            document.getElementById('custom-date-range').style.display = 'none';
            document.getElementById('custom-date-range-to').style.display = 'none';
            document.getElementById('date-from-filter').value = '';
            document.getElementById('date-to-filter').value = '';
            fetchLogs();
        });
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('search-input');
            document.getElementById('search-clear-btn').style.display = input.value ? 'inline' : 'none';
        });
        // Handle pagination links via AJAX
        document.addEventListener('click', function(e) {
            if (e.target.closest('#logs-table-container .pagination a')) {
                e.preventDefault();
                const url = e.target.closest('a').href;
                const container = document.getElementById('logs-table-container');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTable = doc.getElementById('logs-table-container');
                        if (newTable && container) {
                            container.innerHTML = newTable.innerHTML;
                        }
                    });
            }
        });
    </script>
</x-superadmin-layout>