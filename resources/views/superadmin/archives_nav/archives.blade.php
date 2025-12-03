<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Archives') }}
        </h2>
    </x-slot>

    @if(session('success'))
    <div id="flash-success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="py-12" id="main-content">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="flex justify-between items-center mb-4">
                <form method="GET" action="{{ route('archives') }}" id="filters-form" class="flex gap-2">
                    <!-- Province Dropdown -->
                    <div class="relative w-56">
                        <select name="province" id="filter-province" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Province</option>
                            <option value="DAVAO CITY" {{ request('province') == 'DAVAO CITY' ? 'selected' : '' }}>DAVAO CITY</option>
                            <option value="DAVAO DE ORO" {{ request('province') == 'DAVAO DE ORO' ? 'selected' : '' }}>DAVAO DE ORO</option>
                            <option value="DAVAO DEL NORTE" {{ request('province') == 'DAVAO DEL NORTE' ? 'selected' : '' }}>DAVAO DEL NORTE</option>
                            <option value="DAVAO DEL SUR" {{ request('province') == 'DAVAO DEL SUR' ? 'selected' : '' }}>DAVAO DEL SUR</option>
                            <option value="DAVAO OCCIDENTAL" {{ request('province') == 'DAVAO OCCIDENTAL' ? 'selected' : '' }}>DAVAO OCCIDENTAL</option>
                            <option value="DAVAO ORIENTAL" {{ request('province') == 'DAVAO ORIENTAL' ? 'selected' : '' }}>DAVAO ORIENTAL</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Municipality Dropdown -->
                    <div class="relative w-56">
                        <select name="municipality" id="filter-municipality" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Municipality</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Office Dropdown -->
                    <div class="relative w-56">
                        <select name="office" id="filter-office" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Office</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>

                    @if(request('province') || request('municipality') || request('office'))
                        <a href="{{ route('archives') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Clear Filters</a>
                    @endif
                </form>
            </div>

            {{-- Search + Buttons --}}
            <div class="flex justify-between items-center mb-6">
                <div class="max-w-md w-full">
                    <form method="GET" action="{{ route('archives') }}" class="flex gap-2">
                        <div class="relative w-full">
                            <input type="text" name="search" id="searchInput" placeholder="Search by Company ID or Full Name..."
                                   value="{{ request('search') }}"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('archives') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Archived Users Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="overflow-x-auto w-full">
                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left text-xs font-medium text-white uppercase">No.</th>
                                <th class="w-24 px-4 py-3 text-left text-xs font-medium text-white uppercase">Company ID</th>
                                <th class="w-52 px-4 py-3 text-left text-xs font-medium text-white uppercase">Fullname</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-white uppercase">Province</th>
                                <th class="w-28 px-4 py-3 text-left text-xs font-medium text-white uppercase">Municipality</th>
                                <th class="w-32 px-4 py-3 text-left text-xs font-medium text-white uppercase">Date Archived</th>
                                <th class="w-36 px-4 py-3 text-center text-xs font-medium text-white uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($archivedUsers as $index => $user)
                            <tr id="archive-row-{{ $user->id }}" class="hover:bg-gray-100">
                                <td class="px-4 py-3">{{ $archivedUsers->firstItem() + $index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->company_id }}</td>
                                <td class="px-4 py-3 text-left whitespace-nowrap">
                                    <button onclick="openUserProfile('{{ $user->id }}')" type="button"
                                        class="text-black hover:text-blue-800 hover:underline font-medium cursor-pointer">
                                        {{ $user->fullname }}
                                    </button>
                                    @if($user->email)
                                        <br><span class="text-xs text-gray-500">{{ $user->email }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->province }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->municipality }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    {{ $user->archived_at ? $user->archived_at->format('m/d/Y') : '' }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button onclick="openHistoryModal('{{ $user->id }}')" 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-1 px-3 rounded mr-2">
                                        History
                                    </button>
                                    <button onclick="restoreArchivedUser(event, '{{ $user->id }}')" 
                                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 px-3 rounded">
                                        Restore
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No archived users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $archivedUsers->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- User Profile Modal Overlay -->
    <div id="user-profile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <!-- User Profile Modal -->
        <div id="user-profile-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto relative">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 bg-gray-800 text-white rounded-t-lg">
                    <h3 class="text-lg font-semibold">User Profile</h3>
                    <button onclick="closeUserProfile()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div id="user-profile-content">
                        <!-- User Profile content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div id="history-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div id="history-modal" class="fixed inset-0 z-60 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-7xl w-full mx-4 max-h-[90vh] flex flex-col relative">
                
                <!-- Sticky Header -->
                <div class="flex items-center justify-between p-6 border-b bg-gray-800 sticky top-0 z-10">
                    <h3 id="history-modal-title" class="text-2xl font-bold text-white">User History</h3>
                    <button onclick="closeHistoryModal()" class="text-gray-300 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="p-6 overflow-y-auto flex-1" id="history-modal-content">
                    <div class="text-center text-gray-600 py-8">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast (unchanged) -->

    <script>
        // quick helpers
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
        function showToast(msg, timeout = 2500) {
            const t = document.getElementById('toast');
            const inner = document.getElementById('toast-inner');
            inner.textContent = msg;
            t.classList.remove('hidden');
            t.classList.add('block');
            setTimeout(() => {
                t.classList.add('hidden');
                t.classList.remove('block');
            }, timeout);
        }

        /* -------------------
        Helper: populate select options (small utility)
        ------------------- */
        function populateSelectOptions(selectElem, items, placeholderText = 'Select', selectedValue = '') {
            if (!selectElem) return;
            selectElem.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderText;
            selectElem.appendChild(placeholder);
            if (!Array.isArray(items) || items.length === 0) return;
            items.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                if (selectedValue !== undefined && selectedValue !== null && selectedValue.toString() === v.toString()) {
                    opt.selected = true;
                }
                selectElem.appendChild(opt);
            });
        }

        /* -------------------
        DATA MAPPINGS (dynamic from database via Place model)
        ------------------- */
        // Dynamic data from database via Place model
        const provinceMunicipalityMap = @json($provinceMunicipalityMap);

        // Dynamic data from database via Place model
        const officeMap = @json($officeMap);

        /* ================================
        FILTER FUNCTIONS FOR ARCHIVES PAGE
        ================================ */
        function loadProvincesFilter(selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            populateSelectOptions(sel, Object.keys(provinceMunicipalityMap), 'Filter by Province', selected ?? '');
        }
        function loadMunicipalitiesFilter(province, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = provinceMunicipalityMap[province] || [];
            populateSelectOptions(sel, list, 'Filter by Municipality', selected ?? '');
        }
        function loadOfficesFilter(municipality, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = officeMap[municipality] || [];
            populateSelectOptions(sel, list, 'Filter by Office', selected ?? '');
        }

        /* -------------------
        Safe initial filter values (from Blade -> JS)
        ------------------- */
        const initialFilterProvince     = '{{ request("province", "") }}';
        const initialFilterMunicipality = '{{ request("municipality", "") }}';
        const initialFilterOffice       = '{{ request("office", "") }}';

        /* -------------------
        DOMContentLoaded: wire Archives Filters
        ------------------- */
        document.addEventListener('DOMContentLoaded', function() {
            const fProv = document.getElementById('filter-province');
            const fMun  = document.getElementById('filter-municipality');
            const fOff  = document.getElementById('filter-office');

            if (fProv) {
                loadProvincesFilter('filter-province', initialFilterProvince || '');
                // if there's an initial province, load municipalities and offices
                if (initialFilterProvince) {
                    loadMunicipalitiesFilter(initialFilterProvince, 'filter-municipality', initialFilterMunicipality || '');
                } else {
                    populateSelectOptions(fMun, [], 'Filter by Municipality');
                }
                if (initialFilterMunicipality) {
                    loadOfficesFilter(initialFilterMunicipality, 'filter-office', initialFilterOffice || '');
                } else {
                    populateSelectOptions(fOff, [], 'Filter by Office');
                }

                fProv.addEventListener('change', function(e) {
                    const prov = e.target.value;
                    loadMunicipalitiesFilter(prov, 'filter-municipality');
                    populateSelectOptions(fOff, [], 'Filter by Office'); // clear offices when province changes
                });
                fMun && fMun.addEventListener('change', function(e) {
                    loadOfficesFilter(e.target.value, 'filter-office');
                });
            }
        });

        // --- User Profile Modal functions ---
        function openUserProfile(userId) {
            document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading profile...</p></div>';
            document.getElementById('user-profile-overlay').classList.remove('hidden');
            document.getElementById('user-profile-modal').classList.remove('hidden');
            document.getElementById('main-content').classList.add('blur-md');

            fetch(`/users/${userId}/profile`, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        document.getElementById('user-profile-content').innerHTML = generateUserProfileHTML(data.user);
                    } else {
                        document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                });
        }

        function closeUserProfile() {
            document.getElementById('user-profile-overlay').classList.add('hidden');
            document.getElementById('user-profile-modal').classList.add('hidden');
            document.getElementById('main-content').classList.remove('blur-md');
        }

        function generateUserProfileHTML(user) {
            return `
                <div>
                    <div class="flex items-start space-x-6 mb-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">${user.fullname ?? ''}</h2>
                            <p class="text-gray-600 mb-1">${user.company_id ?? ''}</p>
                            <p class="text-black font-medium mb-4">${user.access_level ?? ''}</p>
                        </div>
                    </div>
                    <hr class="my-4 border-black border-t-2">
                    <div class="flex w-full">
                        <div class="space-y-3 flex-1">
                            <div><span class="font-bold text-black">Email:</span> <span class="ml-2 text-gray-900">${user.email ?? ''}</span></div>
                            <div><span class="font-bold text-black">Username:</span> <span class="ml-2 text-gray-900">${user.username ?? ''}</span></div>
                            <div><span class="font-bold text-black">Employee Status:</span> <span class="ml-2 text-gray-900">${user.employee_status ?? ''}</span></div>
                            <div><span class="font-bold text-black">Locked Status:</span> <span class="ml-2 text-gray-900">${user.locked_status ?? ''}</span></div>
                            <div><span class="font-bold text-black">Activated:</span> <span class="ml-2 text-gray-900">${user.activated ?? ''}</span></div>
                        </div>
                        <div class="space-y-3 flex-1">
                            <div><span class="font-bold text-black">Region:</span> <span class="ml-2 text-gray-900">${user.region ?? ''}</span></div>
                            <div><span class="font-bold text-black">Province:</span> <span class="ml-2 text-gray-900">${user.province ?? ''}</span></div>
                            <div><span class="font-bold text-black">Municipality:</span> <span class="ml-2 text-gray-900">${user.municipality ?? ''}</span></div>
                            <div><span class="font-bold text-black">Office:</span> <span class="ml-2 text-gray-900">${user.office ?? ''}</span></div>
                        </div>
                    </div>
                </div>
            `;
        }

        // --- History modal & restore logic ---
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '{{ csrf_token() }}';
        // --- History Modal ---
        async function openHistoryModal(userId) {
        // show spinner + open modal
        document.getElementById('history-modal-content').innerHTML =
            '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading history...</p></div>';
        document.getElementById('history-modal-overlay').classList.remove('hidden');
        document.getElementById('history-modal').classList.remove('hidden');
        document.getElementById('main-content').classList.add('blur-md');

        try {
            const res = await fetch(`/users/${userId}/history`, {
                headers: { 'Accept': 'application/json' },
                method: 'GET',
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('History API error', res.status, text);
                document.getElementById('history-modal-title').textContent = 'History (Error)';
                document.getElementById('history-modal-content').innerHTML =
                    `<div class="p-4 text-red-600"><strong>Server error ${res.status}</strong><pre class="whitespace-pre-wrap text-sm mt-2">${escapeHtml(text.slice(0,2000))}</pre></div>`;
                return;
            }

            const contentType = res.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                const data = await res.json();

                if (!data || !data.success) {
                    console.error('History API returned success=false', data);
                    document.getElementById('history-modal-title').textContent = 'History (No data)';
                    const msg = data && data.message ? escapeHtml(data.message) : 'Unable to load history.';
                    document.getElementById('history-modal-content').innerHTML =
                        `<div class="p-4 text-red-600">${msg}</div>`;
                    return;
                }

                // update header title (bigger font)
                const titleEl = document.getElementById('history-modal-title');
                titleEl.textContent = `${escapeHtml(data.user.fullname ?? data.user.company_id ?? 'User')}`;
                titleEl.className = 'text-2xl font-bold text-white';

                const { units = [], fets = [] } = (data.history || {});
                let html = '';

                // Units table
                html += `<h5 class="font-medium mb-2">Units Handled</h5>`;
                if (units.length) {
                    html += `<div class="overflow-x-auto mb-6"><table class="min-w-full border border-gray-300 text-sm"><thead class="bg-gray-100"><tr>
                        <th class="px-3 py-2 border">Property No</th>
                        <th class="px-3 py-2 border">Description</th>
                        <th class="px-3 py-2 border">Serial No</th>
                        <th class="px-3 py-2 border">PAR No</th>
                        <th class="px-3 py-2 border">Office</th>
                        <th class="px-3 py-2 border">Receiver</th>
                    </tr></thead><tbody>`;
                    units.forEach(u => {
                        html += `<tr>
                            <td class="px-3 py-2 border">${escapeHtml(u.PROPERTY_NO ?? u.property_no ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(u.GENERAL_DESCRIPTION ?? u.general_description ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(u.SERIAL_NO ?? u.serial_no ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(u.PAR_NO ?? u.par_no ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(u.OFFICE ?? u.office ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(u.RECEIVER ?? u.receiver ?? '')}</td>
                        </tr>`;
                    });
                    html += `</tbody></table></div>`;
                } else {
                    html += `<div class="text-sm text-gray-500 mb-6">No units recorded.</div>`;
                }

                // FETS table
                html += `<h5 class="font-medium mb-2">FETS Documents Requested</h5>`;
                if (fets.length) {
                    html += `<div class="overflow-x-auto"><table class="min-w-full border border-gray-300 text-sm"><thead class="bg-gray-100"><tr>
                        <th class="px-3 py-2 border">FETS No</th>
                        <th class="px-3 py-2 border">Property No</th>
                        <th class="px-3 py-2 border">Remarks</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Date Submitted</th>
                        <th class="px-3 py-2 border">Preview</th>
                    </tr></thead><tbody>`;
                    fets.forEach(f => {
                        let status = (f.status ?? '').toLowerCase();
                        let badgeClass = 'bg-gray-100 text-gray-800';
                        let statusLabel = status.charAt(0).toUpperCase() + status.slice(1);

                        if (status === 'submitted') badgeClass = 'bg-blue-100 text-blue-800';
                        else if (status === 'verified') badgeClass = 'bg-purple-100 text-purple-800';
                        else if (status === 'approved') badgeClass = 'bg-green-100 text-green-800';
                        else if (status === 'rejected') badgeClass = 'bg-red-100 text-red-800';

                        html += `<tr>
                            <td class="px-3 py-2 border">${escapeHtml(f.fets_no ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(f.property_no ?? '')}</td>
                            <td class="px-3 py-2 border">${escapeHtml(f.remarks ?? '')}</td>
                            <td class="px-3 py-2 border">
                                <span class="px-2 py-1 text-xs font-semibold rounded ${badgeClass}">
                                    ${statusLabel || 'N/A'}
                                </span>
                            </td>
                            <td class="px-3 py-2 border">${f.created_at ? new Date(f.created_at).toLocaleString() : ''}</td>
                            <td class="px-3 py-2 border text-center">
                                <a href="/fets/preview/${f.id}" target="_blank"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded shadow">
                                Preview
                                </a>
                            </td>
                        </tr>`;
                    });
                    html += `</tbody></table></div>`;
                } else {
                    html += `<div class="text-sm text-gray-500">No FETS recorded.</div>`;
                }

                document.getElementById('history-modal-content').innerHTML = html;
                return;
            }

            const text = await res.text();
            console.error('History endpoint returned non-JSON content:', text.slice(0,2000));
            document.getElementById('history-modal-content').innerHTML =
                `<div class="p-4 text-red-600"><pre class="whitespace-pre-wrap">${escapeHtml(text.slice(0,2000))}</pre></div>`;
        } catch (err) {
            console.error('Network or parsing error while loading history:', err);
            document.getElementById('history-modal-title').textContent = 'History (Error)';
            document.getElementById('history-modal-content').innerHTML =
                `<div class="p-4 text-red-600">Error loading history: ${escapeHtml(err.message ?? 'unknown')}</div>`;
        }
    }

    function closeHistoryModal() {
        document.getElementById('history-modal-overlay').classList.add('hidden');
        document.getElementById('history-modal').classList.add('hidden');
        document.getElementById('main-content').classList.remove('blur-md');
        // clear content (optional)
        document.getElementById('history-modal-content').innerHTML = '<div class="text-center text-gray-600 py-8">Loading...</div>';
        document.getElementById('history-modal-title').textContent = 'User History';
    }

    async function restoreArchivedUser(event, userId) {
        event.preventDefault();

        if (!confirm('Are you sure you want to restore this user?')) {
            return;
        }

        try {
            const res = await fetch(`/users/${userId}/unarchive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                const text = await res.text();
                console.error('Restore API error', res.status, text);
                showToast('Error restoring user');
                return;
            }

            const data = await res.json();
            if (data.success) {
                // Redirect to archives route so it reloads with default state
                window.location.href = "{{ route('archives') }}";
            } else {
                showToast(data.message || 'Failed to restore user');
            }
        } catch (err) {
            console.error('Network error while restoring user:', err);
            showToast('Error restoring user');
        }
    }
    </script>
</x-superadmin-layout>
