<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('FETS') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm rounded-lg">

            {{-- Success --}}
            @if(session('success'))
                <div id="success-alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                            @if(session('fets_id'))
                                <div class="mt-3 flex gap-3">
                                    <a href="{{ route('fets.download', ['id' => session('fets_id')]) }}"
                                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                                        Download PDF
                                    </a>
                                    <a href="{{ route('fets.preview', ['id' => session('fets_id')]) }}"
                                       target="_blank"
                                       class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
                                        Preview PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                        <button onclick="closeAlert('success-alert')"
                                class="text-green-500 hover:text-green-700 focus:outline-none transition-colors duration-200 ml-4">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Errors --}}
            @if(session('error'))
                <div id="error-alert" class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded relative">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            {{ session('error') }}
                        </div>
                        <button onclick="closeAlert('error-alert')"
                                class="text-red-500 hover:text-red-700 focus:outline-none transition-colors duration-200 ml-4">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div id="validation-errors-alert" class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded relative">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <ul class="list-disc pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button onclick="closeAlert('validation-errors-alert')"
                                class="text-red-500 hover:text-red-700 focus:outline-none transition-colors duration-200 ml-4">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('fets.generate') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                {{-- Transfer Movement --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Transfer Movement</label>
                    <select id="transfer_movement" name="transfer_movement" required
                            class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('transfer_movement') ? '' : 'selected' }}>-- Select Transfer Movement --</option>
                        
                        @if(auth()->user()->access_level === 'Provincial DPSC' || auth()->user()->access_level === 'Regional DPSC')
                            {{-- Provincial/Regional users: Issue/Transfer --}}
                            <option value="Issue/Transfer" {{ old('transfer_movement') == 'Issue/Transfer' ? 'selected' : '' }}>Issue / Transfer</option>
                        @else
                            {{-- Employee users: Return to Lender --}}
                            <option value="Return to Lender" {{ old('transfer_movement') == 'Return to Lender' ? 'selected' : '' }}>Return to Lender</option>
                        @endif
                        
                        <option value="For Surrender" {{ old('transfer_movement') == 'For Surrender' ? 'selected' : '' }}>For Surrender</option>
                        <option value="For Repair" {{ old('transfer_movement') == 'For Repair' ? 'selected' : '' }}>For Repair</option>
                    </select>
                    <p class="text-xs text-gray-600 mt-1">
                        <strong>Serviceable:</strong> {{ $provincialDisplay }} <br>
                        <strong>Unserviceable:</strong> {{ $headOfPropertyDisplay }} <br>
                        <strong>Repair:</strong> Selected Repair Destination
                    </p>
                </div>

                {{-- Repair Destination --}}
                <div id="repair_destination_wrapper"
                     class="mb-4 {{ old('transfer_movement') == 'For Repair' ? '' : 'hidden' }}">
                    <label class="block font-medium text-sm text-gray-700">Repair Destination</label>
                    <select id="repair_destination" name="repair_destination" class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('repair_destination') ? '' : 'selected' }}>-- Select Repair Destination --</option>
                        @foreach($repairDestinations as $dest)
                            <option value="{{ $dest->name }}" {{ old('repair_destination') == $dest->name ? 'selected' : '' }}>
                                {{ $dest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Receiver (auto-filled by backend) --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">To Accountable Person</label>
                    <input type="text" id="to_receiver_display"
                           value="{{ old('to_receiver_display', '(auto-filled after submission)') }}"
                           class="w-full border rounded p-2 text-sm bg-gray-100 cursor-not-allowed" disabled>
                    <input type="hidden" id="to_receiver" name="to_receiver" value="{{ old('to_receiver') }}">
                </div>

                {{-- Remarks --}}
                <div id="remarks_wrapper"
                     class="mb-4 {{ old('transfer_movement') == 'For Repair' ? 'hidden' : '' }}">
                    <label class="block font-medium text-sm text-gray-700">Remarks</label>
                    <select id="remarks" name="remarks" class="w-full border rounded p-2 text-sm" required>
                        <option value="Serviceable" {{ old('remarks') == 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                        <option value="Unserviceable" {{ old('remarks') == 'Unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                    </select>
                </div>

                {{-- MOVED: Search Filter (now below remarks, above table) --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700 mb-2">Search Equipment</label>
                    <div class="flex items-center gap-3 max-w-sm">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by Description, or Property No..."
                               class="flex-1 border-gray-300 rounded-md shadow-sm text-sm p-3 focus:ring-blue-500 focus:border-blue-500"
                               onkeypress="if(event.key==='Enter') filterEquipment()">
                        <button type="button" onclick="filterEquipment()"
                                class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                        <button type="button" id="clearButton" onclick="clearFilter()"
                                class="bg-gray-500 hover:bg-gray-600 text-white p-3 rounded-md flex-shrink-0"
                                style="display: none;">
                            Clear
                        </button>
                    </div>
                </div>

                {{-- Inventory Table --}}
                <div class="mb-4">
                    {{-- ✅ FIXED: Simplified label text to be fully driven by JS --}}
                    <label id="selectUnitsLabel" class="block font-medium text-sm text-gray-700 mb-2">
                        Select Units to FETS
                    </label>
                    <table class="w-full table-auto text-sm border">
                        <thead class="bg-[#2e3192]">
                        <tr>
                            <th class="p-2">
                                <select name="per_page"
                                        class="border rounded p-1 text-sm w-24"
                                        onchange="changePerPage(this.value)">
                                    <option value="10" {{ (request('per_page') ?? session('per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                                    <option value="20" {{ (request('per_page') ?? session('per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                                    <option value="50" {{ (request('per_page') ?? session('per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                                    <option value="all" {{ (request('per_page') ?? session('per_page', 10)) == 'all' ? 'selected' : '' }}>Show All</option>
                                </select>
                            </th>
                            <th class="p-2 text-white">Property No</th>
                            <th class="p-2 text-white">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($inventory as $item)
                            @php
                                $disabledGeneral = isset($inProcessPropertyNos) && in_array($item->PROPERTY_NO, $inProcessPropertyNos);
                                $disabledRepair  = isset($repairInProcessPropertyNos) && in_array($item->PROPERTY_NO, $repairInProcessPropertyNos)
                                                   && (!isset($returnedFromRepairPropNos) || !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos));
                                $isReturnedFromRepair = isset($returnedFromRepairPropNos) && in_array($item->PROPERTY_NO, $returnedFromRepairPropNos);
                                // The item should have a boolean property $item->is_long attached from the controller
                                $isLong = $item->is_long ?? false;
                            @endphp
                            <tr class="{{ $disabledGeneral || $disabledRepair ? 'bg-gray-100 text-gray-500 italic' : '' }}">
                                <td class="p-2 text-center">
                                    {{-- ✅ FIXED: Check for the more specific repair status FIRST --}}
                                    @if ($disabledRepair)
                                        <span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">
                                            Being Assessed for Repair
                                        </span>
                                    @elseif ($disabledGeneral)
                                        <span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                                            FETS in Process
                                        </span>
                                    @else
                                        {{-- ✅ FIXED: Added data-is-long attribute --}}
                                        <input type="checkbox"
                                               name="selected[]"
                                               value="{{ $item->PROPERTY_NO }}"
                                               class="select-checkbox"
                                               data-is-long="{{ $isLong ? 'true' : 'false' }}">

                                        {{-- We still show the status message, but it no longer blocks the checkbox --}}
                                        @if ($isReturnedFromRepair)
                                            <span class="block text-xs mt-1 text-green-700 font-semibold">
                                                (Returned from Repair)
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="p-2 text-center">{{ $item->PROPERTY_NO }}</td>
                                <td class="p-2 text-center">{{ $item->GENERAL_DESCRIPTION }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center p-2">No equipment available</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Submit --}}
                @php
                    $canSubmit = $provincialDisplay !== 'Provincial DPSC - Not Assigned' &&
                                 $headOfPropertyDisplay !== 'Head of Property - Not Assigned';
                @endphp
                <div class="flex gap-2">
                    <button type="submit"
                            id="submitBtn"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm {{ $canSubmit ? '' : 'opacity-50 cursor-not-allowed' }}"
                        {{ $canSubmit ? '' : 'disabled' }}>
                        Submit FETS Request
                    </button>
                </div>
                @if(!$canSubmit)
                    <div class="text-red-600 mt-2">
                        ⚠️ FETS submission blocked: Missing Provincial DPSC or Head of Property. Contact Superadmin.
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Updated JavaScript --}}
    <script>
        // --- Element Definitions ---
        const movementSelect = document.getElementById('transfer_movement');
        const remarksWrapper = document.getElementById('remarks_wrapper');
        const repairWrapper = document.getElementById('repair_destination_wrapper');
        const form = document.querySelector('form[action="{{ route('fets.generate') }}"]');
        const selectUnitsLabel = document.getElementById('selectUnitsLabel');

        /**
         * ✅ UPDATED: Handles checkbox state based on max allowed items and dynamic limit (12/15).
         */
        function updateCheckboxState() {
            const checkboxes = document.querySelectorAll('input.select-checkbox');
            const checkedBoxes = [...checkboxes].filter(cb => cb.checked);
            const checkedCount = checkedBoxes.length;

            // --- START DYNAMIC LIMIT LOGIC (NEW) ---
            // Check if any *selected* item has the 'data-is-long="true"' attribute
            const hasLongItem = checkedBoxes.some(cb => cb.dataset.isLong === 'true');
            // Set max items: 12 if a long item is selected, 15 otherwise
            const currentMaxItems = hasLongItem ? 12 : 15;
            // --- END DYNAMIC LIMIT LOGIC ---

            const maxReached = checkedCount >= currentMaxItems;

            // Update the main label text dynamically
            if (selectUnitsLabel) {
                const limitText = hasLongItem
                    ? ` (${checkedCount} / 12 max - long descriptions)`
                    : ` (${checkedCount} / 15 max)`;
                selectUnitsLabel.textContent = `Select Units to FETS${limitText}`;
            }

            // Loop over all checkboxes to enforce the limit
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                const isServerLocked = row.classList.contains('bg-gray-100');
                if(isServerLocked) return;

                // Disable if not checked AND the max limit has been reached
                cb.disabled = !cb.checked && maxReached;

                // Add visual cues
                row.classList.toggle('opacity-50', cb.disabled);
                row.classList.toggle('cursor-not-allowed', cb.disabled);

                // Add tooltip
                if (cb.disabled) {
                    cb.title = `Maximum ${currentMaxItems} items allowed.`;
                } else {
                    cb.title = "";
                }
            });
        }


        /**
         * Handles visibility of Repair Destination / Remarks fields.
         */
        function toggleFields() {
            const movement = movementSelect.value;
            const remarksSelect = document.getElementById('remarks');

            if (movement === 'For Repair') {
                repairWrapper.classList.remove('hidden');
                remarksWrapper.classList.add('hidden');
                if(remarksSelect) remarksSelect.required = false;
            } else if (movement === 'Issue/Transfer' || movement === 'Return to Lender') {
                repairWrapper.classList.add('hidden');
                remarksWrapper.classList.remove('hidden');
                if(remarksSelect) {
                    remarksSelect.required = true;
                    remarksSelect.value = 'Serviceable'; // Default for Issue/Transfer and Return to Lender
                }
            } else {
                // For Surrender and others
                repairWrapper.classList.add('hidden');
                remarksWrapper.classList.remove('hidden');
                if(remarksSelect) remarksSelect.required = true;
            }
        }

        /**
         * Handles AJAX request for filtering equipment.
         */
        function filterEquipment() {
            const searchValue = document.querySelector('input[name="search"]').value;
            const perPage = document.querySelector('select[name="per_page"]').value;
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Loading...</td></tr>'; // Loading indicator

            fetch(`{{ route('fets.select') }}?per_page=${perPage}&search=${searchValue}&ajax=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = data.html;
                    // Show/hide clear button
                    const clearButton = document.getElementById('clearButton');
                    if (clearButton) clearButton.style.display = searchValue.trim() !== '' ? 'block' : 'none';
                    // Update URL
                    const url = new URL(window.location);
                    if (searchValue) url.searchParams.set('search', searchValue);
                    else url.searchParams.delete('search');
                    window.history.replaceState({}, '', url);
                    // Re-initialize checkbox listeners and state
                    document.querySelectorAll('input.select-checkbox').forEach(cb =>
                        cb.addEventListener('change', updateCheckboxState));
                    updateCheckboxState(); // Apply state to new rows
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-600">Error loading data</td></tr>';
                });
        }

        /**
         * Clears the search filter and reloads the table.
         */
        function clearFilter() {
            document.querySelector('input[name="search"]').value = '';
            const clearBtn = document.getElementById('clearButton');
            if (clearBtn) clearBtn.style.display = 'none';
            filterEquipment(); // Reload table
        }

        /**
         * Handles changing items per page via AJAX.
         */
        function changePerPage(perPage) {
            const searchValue = document.querySelector('input[name="search"]').value;
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Loading...</td></tr>'; // Loading indicator

            fetch(`{{ route('fets.select') }}?per_page=${perPage}&search=${searchValue}&ajax=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = data.html;
                    // Update URL
                    const url = new URL(window.location);
                    url.searchParams.set('per_page', perPage);
                    window.history.replaceState({}, '', url);
                    // Re-initialize checkbox listeners and state
                    document.querySelectorAll('input.select-checkbox').forEach(cb =>
                        cb.addEventListener('change', updateCheckboxState));
                    updateCheckboxState(); // Apply state to new rows
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-600">Error loading data</td></tr>';
                });
        }

        /**
         * Closes alert messages.
         */
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) alert.remove();
        }

        // --- Event Listeners ---
        document.addEventListener('DOMContentLoaded', function() {
            // Checkbox state listeners (also re-added inside AJAX success)
            document.querySelectorAll('input.select-checkbox').forEach(cb =>
                cb.addEventListener('change', updateCheckboxState));
            updateCheckboxState(); // Initial run

            // Transfer movement field visibility
            movementSelect.addEventListener('change', toggleFields);
            toggleFields(); // Initial run

            // Add loading cursor on form submit
            if (form) {
                form.addEventListener('submit', function() {
                    // Check validity before changing cursor
                    if (form.checkValidity()) {
                        document.body.style.cursor = 'wait';
                        const submitBtn = document.getElementById('submitBtn');
                        if(submitBtn) submitBtn.disabled = true;
                    }
                });
            }
            // Show clear button on load if search exists
            const searchInput = document.querySelector('input[name="search"]');
            const clearButton = document.getElementById('clearButton');
            if (searchInput && clearButton && searchInput.value.trim() !== '') {
                clearButton.style.display = 'block';
            }
        });

        // Reset cursor if user navigates away
        window.addEventListener('beforeunload', () => {
            document.body.style.cursor = 'default';
        });


    </script>
</x-app-layout>
