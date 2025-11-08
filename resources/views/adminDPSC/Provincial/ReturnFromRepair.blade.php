<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Return from Repair FETS') }}
        </h2>
    </x-slot>

    {{-- Main container --}}
    <div class="py-6 overflow-y-auto" style="max-height: calc(100vh - 4.7rem);">
        <div class="w-full px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 w-full">

                {{-- Title and Description --}}
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Return from Repair FETS</h2>
                <p class="text-gray-600 mb-6">
                    Select equipment to be returned. <strong>Only units from the same original owner can be selected in a single FETS.</strong>
                </p>

                {{-- Alerts --}}
                @if(session('success'))
                    <div id="success-alert" class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <p class="text-green-700 font-medium">{{ session('success') }}</p>
                            </div>
                            <button onclick="closeAlert('success-alert')" class="text-green-500 hover:text-green-700 focus:outline-none transition-colors duration-200"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div id="error-alert" class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <p class="text-red-700 font-medium">{{ session('error') }}</p>
                            </div>
                            <button onclick="closeAlert('error-alert')" class="text-red-500 hover:text-red-700 focus:outline-none transition-colors duration-200"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    </div>
                @endif

                {{-- Filter Form --}}
                <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex gap-4 items-center flex-wrap" id="filterForm">
                    {{-- Search Input & Buttons --}}
                    <div class="flex items-center gap-2 flex-grow sm:flex-grow-0">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search Property No, Desc, Serial..."
                               class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-full sm:w-72">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-md flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                        @if(request('search'))
                            <a href="{{ route(Route::currentRouteName()) }}" class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-3 py-2 rounded-md flex-shrink-0">Clear</a>
                        @endif
                    </div>

                    {{-- Per Page Dropdown --}}
                    <div class="ml-auto flex-shrink-0">
                        <select name="per_page" onchange="document.getElementById('filterForm').submit()" class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-auto">
                            <option value="10" {{ (request('per_page') ?? session('return_per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                            <option value="20" {{ (request('per_page') ?? session('return_per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                            <option value="50" {{ (request('per_page') ?? session('return_per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                            {{-- Use totalReturnableCount passed from controller --}}
                            <option value="all" {{ (request('per_page') ?? session('return_per_page', 10)) == 'all' ? 'selected' : '' }}>Show All ({{ $totalReturnableCount ?? 'N/A' }})</option>
                        </select>
                    </div>
                </form>

                {{-- Empty state / No Results Logic --}}
                @if(!isset($returnableUnits) || ($returnableUnits->isEmpty() && !request('search')))
                    <div class="flex flex-col items-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Equipment for Return</h3>
                        <p class="mt-1 text-sm text-gray-500">No equipment is currently available for return from repair in your province.</p>
                    </div>
                @elseif($returnableUnits->isEmpty() && request('search'))
                    <p class="text-center text-gray-600 py-4">No results found matching your search criteria.</p>
                @else
                    {{-- Main Form --}}
                    <form action="{{ route('fets.return.submit') }}" method="POST" id="returnForm">
                        @csrf
                        <div class="mb-4 overflow-x-auto">
                            {{-- ✅ MODIFIED: Label is simpler; JS will populate count/max --}}
                            <label id="selectUnitsLabel" class="block font-medium text-sm text-gray-700 mb-2">
                                Select Units to Return
                            </label>
                            <table class="w-full table-auto text-sm border">
                                <thead class="bg-[#2e3192]">
                                <tr>
                                    <th class="p-2 text-white">Select</th>
                                    <th class="p-2 text-white">Property No</th>
                                    <th class="p-2 text-white text-left">Description</th>
                                    <th class="p-2 text-white text-left">Original Owner</th>
                                    <th class="p-2 text-white">Submitted FETS ID</th>
                                    <th class="p-2 text-white">Status</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($returnableUnits as $unit)
                                    @php
                                        $isLocked = in_array($unit->PROPERTY_NO, $lockedPropNos ?? []);
                                        $isRestored  = !empty($unit->DPO_REMARKS) && str_contains($unit->DPO_REMARKS, 'Returned from Repair');
                                        $fets = $fetsForRepair->first(function($f) use ($unit) {
                                             $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
                                             return in_array($unit->PROPERTY_NO, $propNos);
                                        });
                                        $originalSubmitter = $unitSubmitters[$unit->PROPERTY_NO] ?? 'Unknown';
                                        $fetsId = $fets ? $fets->created_at->format('Ymd') . '-' . $fets->id : 'N/A';
                                    @endphp
                                    <tr class="{{ ($isLocked || $isRestored) ? 'bg-gray-100 text-gray-500 italic' : '' }}">
                                        <td class="p-2 text-center">
                                            @if ($isLocked)
                                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">Return in Process</span>
                                            @elseif ($isRestored)
                                                <span class="text-xs text-green-600 font-semibold">Returned</span>
                                            @else
                                                {{-- ✅ MODIFIED: Added data-is-long attribute --}}
                                                <input type="checkbox"
                                                       name="selected[]"
                                                       value="{{ $unit->PROPERTY_NO }}"
                                                       class="select-checkbox form-checkbox h-4 w-4 text-blue-600"
                                                       data-submitter="{{ $originalSubmitter }}"
                                                       data-is-long="{{ $unit->is_long ? 'true' : 'false' }}">
                                            @endif
                                        </td>
                                        <td class="p-2 text-center">{{ $unit->PROPERTY_NO }}</td>
                                        <td class="p-2 text-left">{{ $unit->GENERAL_DESCRIPTION }}</td>
                                        <td class="p-2 text-left">{{ $originalSubmitter }}</td>
                                        <td class="p-2 text-center">{{ $fetsId }}</td>
                                        <td class="p-2 text-center whitespace-nowrap">
                                            @if($isLocked)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Processing</span>
                                            @elseif($isRestored)
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Returned</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ready</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        @if($returnableUnits instanceof \Illuminate\Pagination\LengthAwarePaginator && $returnableUnits->hasPages())
                            <div class="mt-4">
                                {{ $returnableUnits->appends(request()->query())->links() }}
                            </div>
                        @endif

                        {{-- Submit section --}}
                        <div class="mt-6 border-t pt-6">
                            <div class="flex items-center justify-between">
                                <button type="submit" id="submitBtn"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    Submit Return
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ✅ MODIFIED: Script Section --}}
    <script>
        // Function to close alert messages
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }

        // Handle checkbox selection and form submission
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = Array.from(document.querySelectorAll('input.select-checkbox'));
            const submitBtn = document.getElementById('submitBtn');
            const returnForm = document.getElementById('returnForm');
            const selectUnitsLabel = document.getElementById('selectUnitsLabel'); // Get label

            // This function now handles all dynamic logic
            function updateCheckboxState() {
                const checkedBoxes = checkboxes.filter(cb => cb.checked);
                const count = checkedBoxes.length;

                // --- START DYNAMIC LIMIT LOGIC ---
                // Check if any *selected* item has the 'data-is-long="true"' attribute
                const hasLongItem = checkedBoxes.some(cb => cb.dataset.isLong === 'true');
                // Set max items: 12 if a long item is selected, 15 otherwise
                const currentMaxItems = hasLongItem ? 12 : 15;
                // --- END DYNAMIC LIMIT LOGIC ---

                // Update UI label with dynamic count and max
                if (selectUnitsLabel) {
                    selectUnitsLabel.textContent = `Select Units to Return (${count} / ${currentMaxItems} max)`;
                }

                // Enable/disable submit button
                if (submitBtn) submitBtn.disabled = count === 0;

                // Logic for "same submitter" rule
                const selectedSubmitter = checkedBoxes[0]?.dataset?.submitter || null;
                // Logic for "max items reached" rule (using dynamic limit)
                const maxReached = count >= currentMaxItems;

                // Loop over all checkboxes to enable/disable them
                checkboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    // Skip server-locked rows
                    const isServerLocked = row.classList.contains('bg-gray-100');
                    if (isServerLocked) return;

                    // Check for disabling conditions
                    const disabledBySubmitter = selectedSubmitter && cb.dataset.submitter !== selectedSubmitter;
                    const disabledByLimit = !cb.checked && maxReached;

                    cb.disabled = disabledBySubmitter || disabledByLimit;

                    // Apply styling to disabled rows
                    row.classList.toggle('opacity-50', cb.disabled);
                    row.classList.toggle('cursor-not-allowed', cb.disabled);
                    row.classList.toggle('bg-yellow-50', !cb.disabled && !cb.checked);

                    // Set helpful titles on disabled checkboxes
                    if (disabledBySubmitter) {
                        cb.title = "Cannot select units from different employees.";
                    } else if (disabledByLimit) {
                        cb.title = `Maximum ${currentMaxItems} items allowed.`; // Use dynamic max
                    } else {
                        cb.title = "";
                    }
                });
            }

            // Add event listener to every checkbox
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCheckboxState);
            });

            // Run once on page load to set initial state (e.g., "0 / 15 max")
            updateCheckboxState();

            // Form submission validation
            if (returnForm) {
                returnForm.addEventListener('submit', function(e) {
                    const checked = checkboxes.filter(cb => cb.checked);

                    // Check for 0 items
                    if (checked.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one equipment item to return.');
                        return false;
                    }

                    // --- START DYNAMIC VALIDATION ---
                    // Re-calculate the max limit just before submitting
                    const hasLongItem = checked.some(cb => cb.dataset.isLong === 'true');
                    const currentMaxItems = hasLongItem ? 12 : 15;
                    // --- END DYNAMIC VALIDATION ---

                    // Check if over dynamic limit
                    if (checked.length > currentMaxItems) {
                        e.preventDefault();
                        alert(`Please select no more than ${currentMaxItems} equipment items.`);
                        return false;
                    }

                    // Show loading cursor
                    document.body.style.cursor = 'wait';
                    if(submitBtn) submitBtn.disabled = true;
                });
            }

            // Reset cursor on page leave
            window.addEventListener('beforeunload', () => {
                document.body.style.cursor = 'default';
            });
        });
    </script>
</x-ProvincialAdmin-layout>
