<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

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
                                        📥 Download PDF
                                    </a>
                                    <a href="{{ route('fets.preview', ['id' => session('fets_id')]) }}"
                                       target="_blank"
                                       class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">
                                        👁️ Preview PDF
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
            <form action="{{ route('fets.generate') }}" method="POST">
                @csrf

                {{-- Transfer Movement --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Transfer Movement</label>
                    <select id="transfer_movement" name="transfer_movement" required
                            class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('transfer_movement') ? '' : 'selected' }}>-- Select Transfer Movement --</option>
                        <option value="Return to Lender" {{ old('transfer_movement') == 'Return to Lender' ? 'selected' : '' }}>Return to Lender</option>
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
                    <label class="block font-medium text-sm text-gray-700 mb-2">Select Equipment (max 5)</label>
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
                                // Minor Improvement: Use `isset` for robustness, though `?? []` already handles it.
                                $disabledGeneral = isset($inProcessPropertyNos) && in_array($item->PROPERTY_NO, $inProcessPropertyNos);
                                $disabledRepair  = isset($repairInProcessPropertyNos) && in_array($item->PROPERTY_NO, $repairInProcessPropertyNos)
                                                   && (!isset($returnedFromRepairPropNos) || !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos));
                                $isReturnedFromRepair = isset($returnedFromRepairPropNos) && in_array($item->PROPERTY_NO, $returnedFromRepairPropNos);
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
                                        {{-- This block now correctly handles both normal AND returned-from-repair items --}}
                                        <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}" class="select-checkbox">

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
                <button type="submit"
                        id="submitBtn"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm {{ $canSubmit ? '' : 'opacity-50 cursor-not-allowed' }}"
                        {{ $canSubmit ? '' : 'disabled' }}>
                    Submit FETS Request
                </button>
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
        function updateCheckboxState() {
            const checkboxes = document.querySelectorAll('input.select-checkbox');
            const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
            checkboxes.forEach(cb => {
                const row = cb.closest('tr');
                cb.disabled = !cb.checked && checkedCount >= 5;
                row.classList.toggle('opacity-50', cb.disabled);
                row.classList.toggle('cursor-not-allowed', cb.disabled);
            });
        }
        document.querySelectorAll('input.select-checkbox').forEach(cb =>
            cb.addEventListener('change', updateCheckboxState));
        document.addEventListener('DOMContentLoaded', updateCheckboxState);

        // Transfer Movement Logic
        const movementSelect = document.getElementById('transfer_movement');
        const remarksWrapper = document.getElementById('remarks_wrapper');
        const repairWrapper = document.getElementById('repair_destination_wrapper');

        function toggleFields() {
            const movement = movementSelect.value;
            if (movement === 'For Repair') {
                repairWrapper.classList.remove('hidden');
                remarksWrapper.classList.add('hidden');
            } else {
                repairWrapper.classList.add('hidden');
                remarksWrapper.classList.remove('hidden');
            }
        }

        movementSelect.addEventListener('change', toggleFields);
        document.addEventListener('DOMContentLoaded', toggleFields);

        // Updated filter functions
        function filterEquipment() {
            const searchValue = document.querySelector('input[name="search"]').value;
            const perPage = document.querySelector('select[name="per_page"]').value;

            // Show loading indicator
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Loading...</td></tr>';

            // Make AJAX request
            fetch(`{{ route('fets.select') }}?per_page=${perPage}&search=${searchValue}&ajax=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = data.html;

                // Show clear button only if there's a search value
                const clearButton = document.getElementById('clearButton');
                if (searchValue.trim() !== '' && clearButton) {
                    clearButton.style.display = 'block';
                }

                // Update URL without reload
                const url = new URL(window.location);
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }
                window.history.replaceState({}, '', url);

                // Re-initialize checkbox functionality
                document.querySelectorAll('input.select-checkbox').forEach(cb =>
                    cb.addEventListener('change', updateCheckboxState)
                );
                updateCheckboxState();
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-600">Error loading data</td></tr>';
            });
        }

        function clearFilter() {
            // Clear the search input
            document.querySelector('input[name="search"]').value = '';

            // Hide the clear button
            const clearBtn = document.getElementById('clearButton');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }

            // Trigger filter to reload table
            filterEquipment();
        }

        function changePerPage(perPage) {
            const searchValue = document.querySelector('input[name="search"]').value;

            // Show loading indicator
            const tbody = document.querySelector('tbody');
            tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4">Loading...</td></tr>';

            // Make AJAX request
            fetch(`{{ route('fets.select') }}?per_page=${perPage}&search=${searchValue}&ajax=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = data.html;

                // Update URL without reload
                const url = new URL(window.location);
                url.searchParams.set('per_page', perPage);
                window.history.replaceState({}, '', url);

                // Re-initialize checkbox functionality
                document.querySelectorAll('input.select-checkbox').forEach(cb =>
                    cb.addEventListener('change', updateCheckboxState)
                );
                updateCheckboxState();
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="3" class="text-center p-4 text-red-600">Error loading data</td></tr>';
            });
        }

        // Function to close alert messages
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }
    </script>
</x-app-layout>
