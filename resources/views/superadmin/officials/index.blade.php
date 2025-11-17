<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('System Management') }}
        </h2>
    </x-slot>

    {{-- Notification --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 flex items-center justify-between" x-transition>
            <span>{!! session('success') !!}</span>
            <button x-on:click="show = false" class="ml-4 text-green-900 hover:text-red-600 font-bold text-2xl leading-none" style="margin-left:auto;">&times;</button>
        </div>
    @endif

    <div class="p-6" x-data="officialsHistory()">
        {{-- Tab Navigation --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <button onclick="showTab('officials')" id="tab-officials"
                        class="px-4 py-2 border-b-2 font-medium text-sm transition border-blue-500 text-blue-600">
                    <i class="fas fa-user-shield mr-2"></i>Officials Management
                </button>
                <button onclick="showTab('places')" id="tab-places"
                        class="px-4 py-2 border-b-2 font-medium text-sm transition border-transparent text-gray-500">
                    <i class="fas fa-map-marked-alt mr-2"></i>Places Management
                </button>
                <button onclick="showTab('repair')" id="tab-repair"
                        class="px-4 py-2 border-b-2 font-medium text-sm transition border-transparent text-gray-500">
                    <i class="fas fa-tools mr-2"></i>Repair Destination Management
                </button>
                <button onclick="showTab('manual')" id="tab-manual"
                        class="px-4 py-2 border-b-2 font-medium text-sm transition border-transparent text-gray-500">
                    <i class="fas fa-book mr-2"></i>Manual Management
                </button>
            </nav>
        </div>

        {{-- Officials Management Tab --}}
        <div id="content-officials" class="tab-content">
        <div class="space-y-6">
            {{-- Provincial DPSC Table --}}
            @php $byProvince = $officials['Provincial DPSC'] ?? collect([null => collect()]); @endphp
            <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-xl font-semibold mb-3">Provincial DPSC</h2>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#2e3192] text-white">
                            <th class="p-2 text-left">Assigned Province</th>
                            <th class="p-2 text-left">Active Official</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byProvince as $province => $group)
                            @php $active = $group->firstWhere('active', true); @endphp
                            <tr class="border-t">
                                <td class="p-2">{{ $province ?? '-' }}</td>
                                <td class="p-2">
                                    @if($active)
                                        <span class="font-semibold text-green-700">
                                            {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 italic">No active official</span>
                                    @endif
                                </td>
                                <td class="p-2 text-center">
                                    <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                        data-role="Provincial DPSC"
                                        data-province="{{ $province ?? '' }}"
                                        data-active-id="{{ $active ? $active->id : '' }}">
                                        Replace
                                    </button>
                                    <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                        onclick="openHistoryModal('Provincial DPSC', '{{ $province }}')">
                                        View History
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Regional DPSC Table --}}
            @php 
                $regionalGroup = $officials['Regional DPSC'] ?? collect([null => collect()]);
                $regionalActive = $regionalGroup->flatten()->firstWhere('active', true);
                // Try to find user by matching fullname (case-insensitive, handle formatting differences)
                if ($regionalActive && !$regionalActive->user) {
                    $nameParts = explode(' ', trim($regionalActive->fullname));
                    $lastName = $nameParts[0] ?? '';
                    $regionalActive->load(['user' => function($q) use ($lastName) {
                        $q->where('fullname', 'LIKE', '%' . $lastName . '%');
                    }]);
                    // If still no user, try direct search
                    if (!$regionalActive->user) {
                        $regionalActive->user = \App\Models\User::where('fullname', 'LIKE', '%Mozo%')
                            ->orWhere('fullname', 'LIKE', '%' . str_replace([',', '.'], '', $regionalActive->fullname) . '%')
                            ->first();
                    }
                }
            @endphp
            <div class="bg-white shadow rounded-xl p-4">
                <h2 class="text-xl font-semibold mb-3">Regional DPSC</h2>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#2e3192] text-white">
                            <th class="p-2 text-left">Assigned Region</th>
                            <th class="p-2 text-left">Active Official</th>
                            <th class="p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regionalGroup as $province => $group)
                            @php $active = $group->firstWhere('active', true); @endphp
                            <tr class="border-t">
                                <td class="p-2">
                                    @if($regionalActive && $regionalActive->user && $regionalActive->user->region)
                                        {{ $regionalActive->user->region }}
                                    @else
                                        Region XI - Davao Region
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if($active)
                                        <span class="font-semibold text-green-700">
                                            {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 italic">No active official</span>
                                    @endif
                                </td>
                                <td class="p-2 text-center">
                                    <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                        data-role="Regional DPSC"
                                        data-province="{{ $province ?? '' }}"
                                        data-active-id="{{ $active ? $active->id : '' }}">
                                        Replace
                                    </button>
                                    <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                        onclick="openHistoryModal('Regional DPSC', '{{ $province }}')">
                                        View History
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Two Column Layout: Head of Property and Recommending --}}
            <div class="flex gap-6">
                {{-- Head of Property Table --}}
                @php $headGroup = $officials['Head of Property'] ?? collect([null => collect()]); @endphp
                <div class="bg-white shadow rounded-xl p-4 flex-1">
                    <h2 class="text-xl font-semibold mb-3">Head of Property</h2>
                    <table class="w-full border-collapse table-fixed">
                        <thead>
                            <tr class="bg-[#2e3192] text-white">
                                <th class="p-2 text-left" style="width: 60%;">Active Official</th>
                                <th class="p-2 text-center" style="width: 40%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($headGroup as $province => $group)
                                @php $active = $group->firstWhere('active', true); @endphp
                                <tr class="border-t">
                                    <td class="p-2">
                                        @if($active)
                                            <span class="font-semibold text-green-700">
                                                {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 italic">No active official</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-center">
                                        <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                            data-role="Head of Property"
                                            data-province="{{ $province ?? '' }}"
                                            data-active-id="{{ $active ? $active->id : '' }}">
                                            Replace
                                        </button>
                                        <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                            onclick="openHistoryModal('Head of Property', '{{ $province }}')">
                                            View History
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Recommending Table --}}
                @php $recGroup = $officials['Recommending'] ?? collect([null => collect()]); @endphp
                <div class="bg-white shadow rounded-xl p-4 flex-1">
                    <h2 class="text-xl font-semibold mb-3">Recommending</h2>
                    <table class="w-full border-collapse table-fixed">
                        <thead>
                            <tr class="bg-[#2e3192] text-white">
                                <th class="p-2 text-left" style="width: 60%;">Active Official</th>
                                <th class="p-2 text-center" style="width: 40%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recGroup as $province => $group)
                                @php $active = $group->firstWhere('active', true); @endphp
                                <tr class="border-t">
                                    <td class="p-2">
                                        @if($active)
                                            <span class="font-semibold text-green-700">
                                                {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 italic">No active official</span>
                                        @endif
                                    </td>
                                    <td class="p-2 text-center">
                                        <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                            data-role="Recommending"
                                            data-province="{{ $province ?? '' }}"
                                            data-active-id="{{ $active ? $active->id : '' }}">
                                            Replace
                                        </button>
                                        <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                            onclick="openHistoryModal('Recommending', '{{ $province }}')">
                                            View History
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Approving Table (Half Width, aligned with Head of Property) --}}
            @php $appGroup = $officials['Approving'] ?? collect([null => collect()]); @endphp
            <div class="bg-white shadow rounded-xl p-4" style="width: calc(50% - 0.75rem);">
                <h2 class="text-xl font-semibold mb-3">Approving</h2>
                <table class="w-full border-collapse table-fixed">
                    <thead>
                        <tr class="bg-[#2e3192] text-white">
                            <th class="p-2 text-left" style="width: 60%;">Active Official</th>
                            <th class="p-2 text-center" style="width: 40%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appGroup as $province => $group)
                            @php $active = $group->firstWhere('active', true); @endphp
                            <tr class="border-t">
                                <td class="p-2">
                                    @if($active)
                                        <span class="font-semibold text-green-700">
                                            {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 italic">No active official</span>
                                    @endif
                                </td>
                                <td class="p-2 text-center">
                                    <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                        data-role="Approving"
                                        data-province="{{ $province ?? '' }}"
                                        data-active-id="{{ $active ? $active->id : '' }}">
                                        Replace
                                    </button>
                                    <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                        onclick="openHistoryModal('Approving', '{{ $province }}')">
                                        View History
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        {{-- Places Management Tab --}}
        <div id="content-places" class="tab-content" style="display:none;">
            @include('superadmin.officials.places_management')
        </div>

        {{-- Repair Destination Management Tab --}}
        <div id="content-repair" class="tab-content" style="display:none;">
            @include('superadmin.officials.repair_destinations', ['destinations' => $destinations])
        </div>

        {{-- Manual Management Tab --}}
        <div id="content-manual" class="tab-content" style="display:none;">
            @include('superadmin.officials.manual_management', ['currentManual' => $currentManual])
        </div>
    </div>        {{-- Manual Management Tab --}}
        <div id="content-manual" class="tab-content" style="display:none;">
            @include('superadmin.officials.manual_management', ['currentManual' => $currentManual])
        </div>
    </div>

    <script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('[id^="tab-"]').forEach(btn => {
            btn.className = 'px-4 py-2 border-b-2 font-medium text-sm transition border-transparent text-gray-500';
        });
        
        // Show selected tab
        document.getElementById('content-' + tabName).style.display = 'block';
        
        // Add active class to clicked button
        document.getElementById('tab-' + tabName).className = 'px-4 py-2 border-b-2 font-medium text-sm transition border-blue-500 text-blue-600';
        
        // Update URL without reloading page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }
    
    // On page load, check URL for tab parameter and show that tab
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        // Check for session active_tab first, then URL param, then default
        @if(session('active_tab'))
        const activeTab = '{{ session("active_tab") }}';
        @else
        const activeTab = urlParams.get('tab') || 'officials'; // Default to officials tab
        @endif
        showTab(activeTab);
    });
    </script>

    <div id="replaceModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-xl w-1/3">
                <h2 class="text-lg font-bold mb-4">Replace Official</h2>
                <form id="replaceForm" method="POST">
                    @csrf @method('POST')
                    <input type="hidden" name="role" id="replaceRole">
                    <input type="hidden" name="province" id="replaceProvince">

                    <div class="mb-4" id="replaceInputContainer">
                        </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeReplaceModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm Replace</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="historyModal" class="fixed inset-0 hidden items-center justify-center bg-black/50" style="z-index: 9999;">
            <div class="bg-white p-6 rounded-xl w-1/2 shadow-2xl max-h-[80vh] overflow-y-auto">
                <h2 class="text-xl font-bold mb-4">
                    History for <span id="historyProvince"></span> - <span id="historyRole"></span>
                </h2>

                <table class="w-full text-left border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2">Full Name</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Date Assigned</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <!-- History rows will be inserted here -->
                    </tbody>
                </table>

                <div class="flex justify-end mt-4">
                    <button onclick="closeHistoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openReplaceModal(role, province, activeId) {
            console.log('Opening Replace Modal:', { role, province, activeId }); // Debug log
            document.getElementById('replaceModal').classList.remove('hidden');
            document.getElementById('replaceRole').value = role;
            document.getElementById('replaceProvince').value = province;
            document.getElementById('replaceForm').action = `/officials/update/${activeId}`;

            let container = document.getElementById('replaceInputContainer');
            container.innerHTML = '';

            // ✅ --- THIS IS THE FIX --- ✅
            // Define which roles should use a simple text input for the name.
            const textBasedRoles = ['Recommending', 'Approving', 'Head of Property'];

            if (textBasedRoles.includes(role)) {
                // This block is for TEXT-BASED roles. It creates an <input type="text">.
                let label = document.createElement('label');
                label.textContent = 'Full Name';
                label.classList.add('block','mb-1','text-sm','font-medium');
                container.appendChild(label);

                let input = document.createElement('input');
                input.name = 'fullname';
                input.type = 'text';
                input.required = true;
                input.classList.add('w-full','border','rounded','p-2');
                container.appendChild(input);

            } else {
                // This block is for USER-BASED roles (DPSCs). It creates a <select> dropdown.
                const timestamp = new Date().getTime(); // Cache busting
                const url = `/eligible-users?role=${encodeURIComponent(role)}&province=${encodeURIComponent(province)}&active_id=${activeId || ''}&_t=${timestamp}`;
                console.log('Fetching eligible users from:', url); // Debug log
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        console.log('Eligible users data received:', data); // Debug log
                        const users = data.users || data; // Handle both old and new format
                        const currentUserId = data.current_user_id;
                        
                        if(users.length === 0){
                            let msg = document.createElement('p');
                            msg.textContent = 'No eligible users found for this role/province.';
                            msg.classList.add('text-red-600','italic');
                            container.appendChild(msg);
                            return;
                        }

                        let label = document.createElement('label');
                        label.textContent = 'Select User';
                        label.classList.add('block','mb-1','text-sm','font-medium');
                        container.appendChild(label);

                        let select = document.createElement('select');
                        select.name = 'user_id';
                        select.required = true;
                        select.classList.add('w-full','border','rounded','p-2');

                        users.forEach(user => {
                            let opt = document.createElement('option');
                            opt.value = user.id;
                            // Pre-select the current official
                            if (currentUserId && user.id == currentUserId) {
                                opt.selected = true;
                            }
                            opt.textContent = `${user.fullname} (${user.province || 'No Province'})`;
                            select.appendChild(opt);
                        });

                        container.appendChild(select);
                    })
                    .catch(err => {
                        let msg = document.createElement('p');
                        msg.textContent = 'Error loading users.';
                        msg.classList.add('text-red-600','italic');
                        container.appendChild(msg);
                        console.error(err);
                    });
            }
        }

        function closeReplaceModal() {
            document.getElementById('replaceModal').classList.add('hidden');
        }

        async function openHistoryModal(role, province) {
            const modal = document.getElementById('historyModal');
            const roleSpan = document.getElementById('historyRole');
            const provinceSpan = document.getElementById('historyProvince');
            const tbody = document.getElementById('historyTableBody');
            
            // Set title
            roleSpan.textContent = role;
            provinceSpan.textContent = province || 'National';
            
            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Fetch history
            const url = `/officials/history/${encodeURIComponent(role)}/${encodeURIComponent(province || '-')}`;
            try {
                const res = await fetch(url);
                const history = await res.json();
                
                // Clear existing rows
                tbody.innerHTML = '';
                
                // Add new rows
                history.forEach(official => {
                    const row = document.createElement('tr');
                    row.classList.add('border-t');
                    
                    const statusClass = official.active ? 'text-green-600 font-semibold' : 'text-gray-500';
                    const statusText = official.active ? 'Active' : 'Inactive';
                    const date = new Date(official.created_at).toLocaleString();
                    
                    let actionHtml = '';
                    if (!official.active) {
                        actionHtml = `
                            <form action="/officials/reactivate/${official.id}" method="POST">
                                @csrf
                                <button type="submit" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                                    Reactivate
                                </button>
                            </form>
                        `;
                    }
                    
                    row.innerHTML = `
                        <td class="p-2">${official.fullname}</td>
                        <td class="p-2"><span class="${statusClass}">${statusText}</span></td>
                        <td class="p-2">${date}</td>
                        <td class="p-2">${actionHtml}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
            } catch (error) {
                console.error('Error loading history:', error);
                tbody.innerHTML = '<tr><td colspan="4" class="p-2 text-red-600">Error loading history</td></tr>';
            }
        }
        
        function closeHistoryModal() {
            const modal = document.getElementById('historyModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function officialsHistory() {
            return {
                showHistory: false,
                selectedRole: '',
                selectedProvince: '',
                historyList: [],
                async loadHistory(role, province) {
                    console.log('loadHistory called', role, province);
                    this.selectedRole = role;
                    this.selectedProvince = province || 'National'; // Use 'National' if province is empty
                    this.showHistory = true;
                    console.log('showHistory set to:', this.showHistory);
                    console.log('Alpine component data:', this.$data);

                    let url = `/officials/history/${encodeURIComponent(role)}/${encodeURIComponent(province || '-')}`;
                    console.log('Fetching from:', url);
                    let res = await fetch(url);
                    this.historyList = await res.json();
                    console.log('History loaded:', this.historyList);
                    
                    // Force check if modal is visible
                    setTimeout(() => {
                        console.log('After timeout - showHistory:', this.showHistory);
                    }, 100);
                }
            }
        }

        // Add event listeners when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a tab parameter in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            
            // If tab parameter exists, switch to that tab
            if (activeTab && ['officials', 'places', 'repair', 'manual'].includes(activeTab)) {
                showTab(activeTab);
            }
            
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('replace-btn')) {
                    const role = e.target.dataset.role;
                    const province = e.target.dataset.province;
                    const activeId = e.target.dataset.activeId;
                    openReplaceModal(role, province, activeId || null);
                }
            });
        });
    </script>

    <style>[x-cloak] { display: none !important; }</style>
</x-superadmin-layout>
