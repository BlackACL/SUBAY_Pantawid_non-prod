<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Return from Repair FETS') }}
        </h2>
    </x-slot>

    <div class="py-6 overflow-y-auto" style="max-height: calc(100vh - 4.7rem);">
        <div class="w-full px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 w-full">

                {{-- Success & Error Alerts (from new layout) --}}
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

                <div class="mb-6">
                    <p class="text-md text-gray-600">
                        Select equipment to be returned. <strong>Only units from the same original owner can be selected in a single FETS.</strong>
                    </p>
                </div>

                @if($returnableUnits->isEmpty())
                    <div class="flex flex-col items-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Equipment for Return</h3>
                        <p class="mt-1 text-sm text-gray-500">No equipment is currently available for return from repair in your province.</p>
                    </div>
                @else
                    <form action="{{ route('fets.return.submit') }}" method="POST" id="returnForm">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#2e3192]">
                                    <tr>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Select</th>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Property No</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">General Description</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Original Owner</th>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Original FETS ID</th>
                                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($returnableUnits as $unit)
                                        @php
                                            $isLocked = in_array($unit->PROPERTY_NO, $lockedPropNos ?? []);
                                            $originalSubmitter = $unitSubmitters[$unit->PROPERTY_NO] ?? 'Unknown';

                                            $originalFets = $fetsForRepair->first(function ($f) use ($unit) {
                                                $propNos = array_map('trim', explode(',', $f->property_no ?? ''));
                                                return in_array($unit->PROPERTY_NO, $propNos);
                                            });
                                            $fetsId = $originalFets ? $originalFets->created_at->format('Ymd') . '-' . $originalFets->id : 'N/A';
                                        @endphp
                                        <tr class="{{ $isLocked ? 'bg-gray-100 text-gray-500' : '' }}">
                                            <td class="px-3 py-4 text-center">
                                                @if($isLocked)
                                                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded">
                                                        Return in Process
                                                    </span>
                                                @else
                                                    <input type="checkbox" name="selected[]" value="{{ $unit->PROPERTY_NO }}"
                                                           class="select-checkbox form-checkbox h-4 w-4 text-blue-600"
                                                           data-submitter="{{ $originalSubmitter }}">
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-900 text-center font-medium">{{ $unit->PROPERTY_NO }}</td>
                                            <td class="px-3 py-4 text-sm text-gray-900">{{ $unit->GENERAL_DESCRIPTION }}</td>
                                            <td class="px-3 py-4 text-sm text-gray-900">{{ $originalSubmitter }}</td>
                                            <td class="px-3 py-4 text-sm text-gray-900 text-center">{{ $fetsId }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                @if($isLocked)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Processing</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 whitespace-nowrap">Ready for Return</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        @if(method_exists($returnableUnits, 'links') && $returnableUnits->hasPages())
                            <div class="mt-4">
                                {{ $returnableUnits->links() }}
                            </div>
                        @endif

                        <div class="mt-6">
                        </div>
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600">
                                    <span id="selectedCount">0</span> item(s) selected (max 5)
                                </p>
                                <button type="submit" id="submitBtn"
                                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    Submit Return FETS
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Function to close alert messages
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }

        // INTEGRATED: Your advanced checkbox logic
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = Array.from(document.querySelectorAll('input.select-checkbox'));
            const selectedCountSpan = document.getElementById('selectedCount');
            const submitBtn = document.getElementById('submitBtn');

            function updateCheckboxState() {
                const checkedBoxes = checkboxes.filter(cb => cb.checked);
                const count = checkedBoxes.length;

                // Update UI elements
                if(selectedCountSpan) selectedCountSpan.textContent = count;
                if(submitBtn) submitBtn.disabled = count === 0;

                // Get the submitter of the first checked box
                const selectedSubmitter = checkedBoxes[0]?.dataset?.submitter || null;

                checkboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    const isLocked = row.classList.contains('bg-gray-100'); // Check if the row is already locked by the server
                    if (isLocked) return; // Don't modify already locked rows

                    // Disable checkboxes from different submitters
                    const disabledBySubmitter = selectedSubmitter && cb.dataset.submitter !== selectedSubmitter;
                    const disabledByLimit = !cb.checked && count >= 5;

                    cb.disabled = disabledBySubmitter || disabledByLimit;

                    // Apply styles for disabled rows
                    row.classList.toggle('opacity-50', disabledBySubmitter || (disabledByLimit && !cb.checked));
                    row.classList.toggle('cursor-not-allowed', disabledBySubmitter || (disabledByLimit && !cb.checked));

                    // Highlight currently selectable rows
                    row.classList.toggle('bg-yellow-50', !disabledBySubmitter && !cb.checked && !cb.disabled);

                    // Add a helpful tooltip for disabled-by-submitter checkboxes
                    if (disabledBySubmitter) {
                        cb.title = "Cannot select units from different employees.";
                    } else if (disabledByLimit && !cb.checked) {
                        cb.title = "You can only select up to 5 items.";
                    } else {
                        cb.title = "";
                    }
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCheckboxState);
            });

            // Initial run to set the state on page load
            updateCheckboxState();

            // Form submission validation (optional, but good practice)
            document.getElementById('returnForm').addEventListener('submit', function(e) {
                const checked = checkboxes.filter(cb => cb.checked);
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one equipment item to return.');
                    return false;
                }
            });
        });
    </script>
</x-ProvincialAdmin-layout>
