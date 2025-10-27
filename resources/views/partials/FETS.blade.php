<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <div class="pb-6">
        <div class="bg-white p-6 shadow-sm rounded-lg">

            {{-- ✅ Success --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                    @if(session('fets_id'))
                        <div class="mt-3 flex gap-3">
                            <a href="{{ route('fets.download', ['id' => session('fets_id')]) }}"
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">📥 Download PDF</a>
                            <a href="{{ route('fets.preview', ['id' => session('fets_id')]) }}" target="_blank"
                               class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">👁️ Preview PDF</a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ✅ Errors --}}
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ✅ Filter --}}
            <form method="GET" action="{{ route('fets.select.embed') }}" class="mb-4 flex gap-4 flex-wrap" id="filterForm">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by Description, or Property No..."
                       class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-72">

                {{-- Pass through edit parameter if present --}}
                @if(request('edit'))
                    <input type="hidden" name="edit" value="{{ request('edit') }}">
                @endif

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">🔍</button>

                @if(request('search'))
                    {{-- Preserve edit parameter in clear link --}}
                    <a href="{{ route('fets.select.embed', array_filter(['edit' => request('edit')])) }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">Clear</a>
                @endif
            </form>

            {{-- ✅ Form --}}
            <form action="{{ $editFets ? route('fets.update') : route('fets.generate') }}" method="POST">
                @csrf
                @if($editFets)
                    @method('PUT') {{-- Use PUT method for updates --}}
                    <input type="hidden" name="edit_fets_id" value="{{ $editFets->id }}">
                @endif
                <input type="hidden" name="embed" value="1">

                {{-- 🔹 Transfer Movement --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Transfer Movement</label>
                    <select id="transfer_movement" name="transfer_movement" required
                            class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == '' ? 'selected' : '' }}>-- Select Transfer Movement --</option>
                        <option value="Issue/Transfer" {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'Issue/Transfer' ? 'selected' : '' }}>Issue / Transfer</option>
                        <option value="For Repair" {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Repair' ? 'selected' : '' }}>For Repair</option>
                        <option value="For Surrender" {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Surrender' ? 'selected' : '' }}>For Surrender</option>
                    </select>
                </div>

                {{-- 🔹 Repair Destination --}}
                <div id="repair_destination_wrapper"
                     class="mb-4 {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Repair' ? '' : 'hidden' }}">
                    <label class="block font-medium text-sm text-gray-700">Repair Destination</label>
                    <select id="repair_destination" name="repair_destination" class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('repair_destination', $prefilledData['repair_destination'] ?? '') == '' ? 'selected' : '' }}>-- Select Repair Destination --</option>
                        @foreach($repairDestinations as $dest)
                            <option value="{{ $dest->name }}" {{ old('repair_destination', $prefilledData['repair_destination'] ?? '') == $dest->name ? 'selected' : '' }}>
                                {{ $dest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 🔹 Receiver --}}
                <div class="mb-4 {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Surrender' ? 'hidden' : '' }}" id="receiverWrapper">
                    <label class="block font-medium text-sm text-gray-700">To Accountable Person</label>
                    <select id="to_receiver" name="to_receiver" class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('to_receiver', $prefilledData['to_receiver'] ?? '') == '' ? 'selected' : '' }}>-- Select Receiver --</option>
                        @foreach ($receivers as $receiver)
                            <option value="{{ $receiver }}" {{ old('to_receiver', $prefilledData['to_receiver'] ?? '') == $receiver ? 'selected' : '' }}>
                                {{ $receiver }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 🔹 Remarks --}}
                <div id="remarks_wrapper"
                     class="mb-4 {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Repair' ? 'hidden' : '' }}">
                    <label class="block font-medium text-sm text-gray-700">Remarks</label>
                    <select id="remarks" name="remarks" class="w-full border rounded p-2 text-sm"> {{-- Removed required for JS toggle --}}
                        <option value="Serviceable" {{ old('remarks', $prefilledData['remarks'] ?? '') == 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                        <option value="Unserviceable" {{ old('remarks', $prefilledData['remarks'] ?? '') == 'Unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                    </select>
                </div>

                {{-- 🔹 Equipment Table --}}
                <div class="mb-4">
                    <label id="selectUnitsLabel" class="block font-medium text-sm text-gray-700">Select Equipment (max 15)</label> {{-- Label updated by JS --}}
                    <table class="w-full table-auto text-sm border">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2">
                                <select name="per_page" form="filterForm" onchange="document.getElementById('filterForm').submit()" class="border rounded p-1 text-sm w-24">
                                    <option value="10" {{ (request('per_page') ?? session('per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                                    <option value="20" {{ (request('per_page') ?? session('per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                                    <option value="50" {{ (request('per_page') ?? session('per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                                    {{-- Use count of ALL equipment for Show All option --}}
                                    <option value="{{ $allEquipment->count() }}" {{ (request('per_page') ?? session('per_page', 10)) == $allEquipment->count() ? 'selected' : '' }}>Show All ({{$allEquipment->count()}})</option>
                                </select>
                            </th>
                            <th class="p-2">Property No</th>
                            <th class="p-2">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($inventory as $item)
                            @php
                                $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
                                $disabledRepair  = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? [])
                                                   && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
                                $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
                                // Check if this item is part of the FETS being edited (should not be disabled)
                                $isBeingEdited = in_array($item->PROPERTY_NO, $editingFetsPropertyNos ?? []);
                            @endphp
                            {{-- Disable row styling only if locked AND not part of the current edit --}}
                            <tr class="{{ ($disabledGeneral || $disabledRepair) && !$isBeingEdited ? 'bg-gray-100 text-gray-500 italic' : '' }}">
                                <td class="p-2 text-center">
                                    {{-- Show locked status only if locked AND not part of the current edit --}}
                                    @if ($disabledRepair && !$isBeingEdited)
                                        <span class="text-xs inline-block bg-orange-100 text-orange-700 px-2 py-1 rounded">Being Assessed for Repair</span>
                                    @elseif ($disabledGeneral && !$isBeingEdited)
                                        <span class="text-xs inline-block bg-yellow-100 text-yellow-700 px-2 py-1 rounded">FETS in Process</span>
                                    @else
                                        {{-- Show checkbox if not locked, or if it's part of the current edit --}}
                                        <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}" class="select-checkbox"
                                               data-is-long="{{ $item->is_long ? 'true' : 'false' }}"
                                            {{-- Check against old input OR prefilled data for checked state --}}
                                            {{ in_array($item->PROPERTY_NO, old('selected', $prefilledData['selected_items'] ?? [])) ? 'checked' : '' }}>
                                        @if ($isReturnedFromRepair)
                                            <span class="block text-xs mt-1 text-green-700 font-semibold">(Returned from Repair)</span>
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
                    {{-- Pagination Links --}}
                    @if($inventory instanceof \Illuminate\Pagination\LengthAwarePaginator && $inventory->hasPages())
                        <div class="mt-4">
                            {{ $inventory->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                {{-- 🔹 Submit --}}
                @php
                    // Keep existing canSubmit logic based on officials
                    $canSubmit = $provincialDisplay !== 'Provincial DPSC - Not Assigned' &&
                                 $headOfPropertyDisplay !== 'Head of Property - Not Assigned';
                @endphp
                <button type="submit"
                        id="submitBtn"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm {{ $canSubmit ? '' : 'opacity-50 cursor-not-allowed' }}"
                    {{ $canSubmit ? '' : 'disabled' }}>
                    {{ $editFets ? 'Update FETS Request' : 'Submit FETS Request' }} {{-- Dynamic Button Text --}}
                </button>
                @if(!$canSubmit)
                    <div class="text-red-600 mt-2">
                        ⚠️ FETS submission blocked: Missing Provincial DPSC or Head of Property. Contact Superadmin.
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ✅ Scripts --}}
    {{-- ✅ Scripts --}}
    <script>
        // --- Element Definitions ---
        const movementSelect = document.getElementById('transfer_movement');
        const remarksWrapper = document.getElementById('remarks_wrapper');
        const remarksSelect  = document.getElementById('remarks');
        const repairWrapper  = document.getElementById('repair_destination_wrapper');
        const receiverWrapper = document.getElementById('receiverWrapper');
        const toReceiverSelect = document.getElementById('to_receiver');
        const form = document.querySelector('form[action^="{{ url('/fets') }}"]'); // More generic form selector
        const selectUnitsLabel = document.getElementById('selectUnitsLabel');
        const filterForm = document.getElementById('filterForm'); // Get the filter form

        // ✅ Pass PHP array to JS
        const editingFetsPropertyNosJS = @json($editingFetsPropertyNos ?? []);

        /**
         * ✅ UPDATED: Dynamically determines max items and handles checkbox state.
         */
        function updateCheckboxState() {
            const checkboxes = document.querySelectorAll('input.select-checkbox');
            const checkedCheckboxes = [...checkboxes].filter(cb => cb.checked);
            const checkedCount = checkedCheckboxes.length;

            let requiresLongTemplate = false;
            checkedCheckboxes.forEach(cb => {
                // Ensure dataset.isLong exists and check its value
                if (cb.dataset && cb.dataset.isLong === 'true') {
                    requiresLongTemplate = true;
                }
            });


            const currentMaxItemsAllowed = requiresLongTemplate ? 12 : 15;
            const maxReached = checkedCount >= currentMaxItemsAllowed;

            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                // Check if row exists before accessing classList
                if (!row) return;

                const isServerLocked = row.classList.contains('bg-gray-100');
                const isBeingEdited = editingFetsPropertyNosJS.includes(cb.value);

                if (!isServerLocked || isBeingEdited) {
                    cb.disabled = !cb.checked && maxReached;
                    row.classList.toggle('opacity-50', cb.disabled);
                    row.classList.toggle('cursor-not-allowed', cb.disabled);

                    if (cb.disabled) {
                        cb.title = `Maximum ${currentMaxItemsAllowed} items allowed ${requiresLongTemplate ? '(long format triggered)' : '(standard format)'}.`;
                    } else {
                        cb.title = "";
                    }
                } else {
                    cb.disabled = true;
                    if (!row.classList.contains('opacity-50')) row.classList.add('opacity-50');
                    if (!row.classList.contains('cursor-not-allowed')) row.classList.add('cursor-not-allowed');
                }
            });

            if (selectUnitsLabel) {
                selectUnitsLabel.textContent = `Select Equipment (max ${currentMaxItemsAllowed}${requiresLongTemplate ? ' - Long Format Triggered' : ''})`;
            }
        }

        /**
         * Handles visibility/requirement of form fields based on transfer movement. (Unchanged)
         */
        function updateFormFields(choice) {
            // ... (Keep the existing logic here)
            repairWrapper.classList.add('hidden');
            remarksWrapper.classList.add('hidden');
            receiverWrapper.classList.add('hidden');
            if (remarksSelect) remarksSelect.required = false;
            const repairDestSelect = document.getElementById('repair_destination');
            if (repairDestSelect) repairDestSelect.required = false;
            if (toReceiverSelect) toReceiverSelect.required = false;

            if (choice === 'For Repair') {
                repairWrapper.classList.remove('hidden');
                if (repairDestSelect) repairDestSelect.required = true;
            } else if (choice === 'Issue/Transfer') {
                remarksWrapper.classList.remove('hidden');
                receiverWrapper.classList.remove('hidden');
                if (remarksSelect) remarksSelect.required = true;
                if (toReceiverSelect) toReceiverSelect.required = true;
                if (!{{ $editFets ? 'true' : 'false' }} && !'{{ old('remarks') }}') {
                    if (remarksSelect) remarksSelect.value = 'Serviceable';
                }
            } else if (choice === 'For Surrender') {
                remarksWrapper.classList.remove('hidden');
                if (remarksSelect) remarksSelect.required = true;
                if (!{{ $editFets ? 'true' : 'false' }} && !'{{ old('remarks') }}') {
                    if (remarksSelect) remarksSelect.value = 'Unserviceable';
                }
            }
        }

        /**
         * ✅ ADDED: Handles AJAX request for filtering equipment (Used by filter form).
         */
        function submitFilterForm() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Loading...</td></tr>'; // Loading indicator

            // Construct URL, keeping potential 'edit' param
            let baseUrl = "{{ route('fets.select.embed') }}";
            let editParam = '{{ request('edit') ? '&edit=' . request('edit') : '' }}'; // Keep edit param
            let fetchUrl = `${baseUrl}?${params}${editParam}&ajax=1`;


            fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = data.html; // HTML includes data-is-long
                    // Update URL (careful with embed context) - maybe skip history update in iframe?
                    // window.history.replaceState({}, '', `${baseUrl}?${params}${editParam}`);

                    // ✅ --- RE-INITIALIZE CHECKBOX LOGIC --- ✅
                    document.querySelectorAll('input.select-checkbox').forEach(cb =>
                        cb.addEventListener('change', updateCheckboxState));
                    updateCheckboxState(); // Apply dynamic state to newly loaded rows
                    // ✅ --- END RE-INITIALIZATION --- ✅

                    // Update pagination if it exists in response
                    const paginationContainer = document.querySelector('#paginationLinks'); // Add id="paginationLinks" to your pagination div
                    if(paginationContainer && data.pagination) {
                        paginationContainer.innerHTML = data.pagination;
                    } else if (paginationContainer) {
                        paginationContainer.innerHTML = ''; // Clear if no pagination
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-600">Error loading data</td></tr>';
                });
        }


        // --- Event Listeners ---
        movementSelect.addEventListener('change', function () {
            updateFormFields(this.value);
        });

        document.addEventListener('DOMContentLoaded', function () {
            updateFormFields(movementSelect.value || '{{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') }}');

            document.querySelectorAll('input.select-checkbox').forEach(cb => {
                cb.addEventListener('change', updateCheckboxState);
            });
            updateCheckboxState(); // Initial run

            if (form) {
                form.addEventListener('submit', function(e) { // Pass event 'e'
                    // Re-enable required fields before submit
                    if(remarksSelect && !remarksWrapper.classList.contains('hidden')) remarksSelect.required = true;
                    const repairDestSelect = document.getElementById('repair_destination');
                    if(repairDestSelect && !repairWrapper.classList.contains('hidden')) repairDestSelect.required = true;
                    if(toReceiverSelect && !receiverWrapper.classList.contains('hidden')) toReceiverSelect.required = true;

                    // Manually check selected count against dynamic limit before submit
                    const checkedCount = [...document.querySelectorAll('input.select-checkbox')].filter(cb => cb.checked).length;
                    let requiresLong = [...document.querySelectorAll('input.select-checkbox:checked')].some(cb => cb.dataset.isLong === 'true');
                    const currentMax = requiresLong ? 12 : 15;
                    if (checkedCount > currentMax) {
                        e.preventDefault(); // Stop submission
                        alert(`Maximum ${currentMax} items allowed ${requiresLong ? '(long format triggered)' : '(standard format)'}. You have selected ${checkedCount}.`);
                        return; // Prevent cursor change
                    }


                    if (form.checkValidity()) {
                        document.body.style.cursor = 'wait';
                        const submitBtn = document.getElementById('submitBtn');
                        if(submitBtn) submitBtn.disabled = true;
                    } else {
                        document.body.style.cursor = 'default';
                        const submitBtn = document.getElementById('submitBtn');
                        if(submitBtn) submitBtn.disabled = false;
                        // Optionally, find and focus the first invalid field
                        form.reportValidity(); // Show native browser validation messages
                    }
                });
            }

            // Listener for the filter form (search, per_page)
            if (filterForm) {
                // Trigger AJAX on submit (prevents full page reload)
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent default GET request
                    submitFilterForm();
                });
                // Trigger AJAX when per_page changes
                const perPageSelect = filterForm.querySelector('select[name="per_page"]');
                if (perPageSelect) {
                    perPageSelect.addEventListener('change', submitFilterForm);
                }
                // Handle Clear button click
                const clearButton = filterForm.querySelector('a[href*="fets.select.embed"]'); // Find the clear link
                if (clearButton) {
                    clearButton.addEventListener('click', function(e) {
                        e.preventDefault(); // Prevent navigation
                        filterForm.querySelector('input[name="search"]').value = ''; // Clear search box
                        submitFilterForm(); // Submit empty search
                    });
                }
            }

            // Initialize TomSelect
            if (typeof TomSelect !== 'undefined' && toReceiverSelect) {
                if (toReceiverSelect.tomselect) { toReceiverSelect.tomselect.destroy(); }
                new TomSelect('#to_receiver', {
                    create: false, placeholder: '-- Select Receiver --', maxOptions: 1000,
                    sortField: { field: 'text', direction: 'asc' }
                });
                let initialReceiver = '{{ old('to_receiver', $prefilledData['to_receiver'] ?? '') }}';
                if (initialReceiver && toReceiverSelect.tomselect) {
                    toReceiverSelect.tomselect.setValue(initialReceiver, true);
                }
            }
        });

        window.addEventListener('beforeunload', () => {
            document.body.style.cursor = 'default';
        });

    </script>

    {{-- TomSelect Script Link --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

</x-app-layout>
