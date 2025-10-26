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

            <input type="hidden" name="to_receiver" id="hidden_receiver">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">🔍</button>

            @if(request('search'))
                <a href="{{ route('fets.select.embed', ['to_receiver' => request('to_receiver')]) }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">Clear</a>
            @endif
        </form>

        {{-- ✅ Form --}}
        @php
            // Determine if we're in edit mode
            $isEditMode = isset($editFets) && $editFets;
            $formAction = $isEditMode ? route('fets.update') : route('fets.generate');
        @endphp
        <form action="{{ $formAction }}" method="POST" id="fetsForm">
            @csrf
            <input type="hidden" name="embed" value="1">
            
            {{-- Hidden field for edit mode --}}
            @if($isEditMode)
                <input type="hidden" name="edit_fets_id" value="{{ $editFets->id }}">
            @endif

            {{-- 🔹 Transfer Movement --}}
            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700">Transfer Movement</label>
                <select id="transfer_movement" name="transfer_movement" required
                        class="w-full border rounded p-2 text-sm">
                    <option value="" disabled {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') ? '' : 'selected' }}>-- Select Transfer Movement --</option>
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
                    <option value="" disabled {{ old('repair_destination', $prefilledData['repair_destination'] ?? '') ? '' : 'selected' }}>-- Select Repair Destination --</option>
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
                    <option value="" disabled {{ old('to_receiver') ? '' : 'selected' }}>-- Select Receiver --</option>
                    @foreach ($receivers as $receiver)
                        <option value="{{ $receiver }}" {{ old('to_receiver') == $receiver ? 'selected' : '' }}>
                            {{ $receiver }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 🔹 Remarks --}}
            <div id="remarks_wrapper"
                 class="mb-4 {{ old('transfer_movement', $prefilledData['transfer_movement'] ?? '') == 'For Repair' ? 'hidden' : '' }}">
                <label class="block font-medium text-sm text-gray-700">Remarks</label>
                <select id="remarks" name="remarks" class="w-full border rounded p-2 text-sm" required>
                    <option value="Serviceable" {{ old('remarks', $prefilledData['remarks'] ?? '') == 'Serviceable' ? 'selected' : '' }}>Serviceable</option>
                    <option value="Unserviceable" {{ old('remarks', $prefilledData['remarks'] ?? '') == 'Unserviceable' ? 'selected' : '' }}>Unserviceable</option>
                </select>
            </div>

            {{-- 🔹 Equipment Table --}}
            <div class="mb-4">
                <label class="block font-medium text-sm text-gray-700">Select Equipment (max 5)</label>
                <table class="w-full table-auto text-sm border">
                    <thead class="bg-[#2e3192]">
                        <tr>
                            <th class="p-2">
                                <select name="per_page" form="filterForm" onchange="document.getElementById('filterForm').submit()" class="border rounded p-1 text-sm w-24">
                                    <option value="10" {{ (request('per_page') ?? session('per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                                    <option value="20" {{ (request('per_page') ?? session('per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                                    <option value="50" {{ (request('per_page') ?? session('per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                                    <option value="{{ $allEquipment->count() }}" {{ (request('per_page') ?? session('per_page', 10)) == $allEquipment->count() ? 'selected' : '' }}>Show All</option>
                                </select>
                            </th>
                            <th class="p-2 text-white">Property No</th>
                            <th class="p-2 text-white">Description</th>
                        </tr>
                    </thead>
<tbody>
    @forelse ($inventory as $item)
        @php
            $disabledGeneral = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
            $disabledRepair  = in_array($item->PROPERTY_NO, $repairInProcessPropertyNos ?? [])
                               && !in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
            $isReturnedFromRepair = in_array($item->PROPERTY_NO, $returnedFromRepairPropNos ?? []);
        @endphp
        <tr class="{{ $disabledGeneral || $disabledRepair ? 'bg-gray-100 text-gray-500 italic' : '' }}">
            <td class="p-2 text-center">
                @if ($disabledGeneral || $disabledRepair)
                    {{-- Item is locked in another FETS, cannot select --}}
                    <span class="text-xs inline-block {{ $disabledRepair ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700' }} px-2 py-1 rounded">
                        {{ $disabledRepair ? 'Being Assessed for Repair' : 'FETS in Process' }}
                    </span>
                @else
                    {{-- Item can be selected --}}
                    <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}" class="select-checkbox"
                           {{ in_array($item->PROPERTY_NO, $editingFetsPropertyNos ?? []) || in_array($item->PROPERTY_NO, old('selected', [])) ? 'checked' : '' }}>

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

            {{-- 🔹 Submit --}}
            @php
                $canSubmit = $provincialDisplay !== 'Provincial DPSC - Not Assigned' &&
                             $headOfPropertyDisplay !== 'Head of Property - Not Assigned';
                $buttonText = $isEditMode ? 'Update FETS Request' : 'Submit FETS Request';
            @endphp
            
            {{-- Edit Mode Banner --}}
            @if($isEditMode)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium text-blue-700">Editing FETS Request</p>
                            <p class="text-sm text-blue-600">You are updating FETS #{{ $editFets->id }}. Changes will update the existing request, not create a new one.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <button type="submit"
                    id="submitBtn"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm {{ $canSubmit ? '' : 'opacity-50 cursor-not-allowed' }}"
                    {{ $canSubmit ? '' : 'disabled' }}>
                {{ $buttonText }}
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

    /**
     * Updates the visibility and requirement status of form fields based on the selected transfer movement.
     */
    function updateFormFields(choice) {
        // --- 1. Start with a default state (hide everything) ---
        repairWrapper.classList.add('hidden');
        remarksWrapper.classList.add('hidden');
        receiverWrapper.classList.add('hidden');
        remarksSelect.required = false;

        // --- 2. Show and configure fields based on the selected choice ---
        if (choice === 'For Repair') {
            repairWrapper.classList.remove('hidden');
            // 'remarks' and 'receiver' stay hidden.

        } else if (choice === 'Issue/Transfer') {
            remarksWrapper.classList.remove('hidden');
            receiverWrapper.classList.remove('hidden'); // Receiver is SHOWN for Issue/Transfer
            remarksSelect.required = true;
            remarksSelect.value = 'Serviceable';

        } else if (choice === 'For Surrender') {
            remarksWrapper.classList.remove('hidden');
            // ✅ **FIXED**: The receiverWrapper is NOT shown, it remains hidden as per the default state.
            remarksSelect.required = true;
            remarksSelect.value = 'Unserviceable';
        }
    }

    // --- Event Listeners ---
    movementSelect.addEventListener('change', function () {
        updateFormFields(this.value);
        notifyParentOfFormChange();
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize the form state on page load based on the current or old value
        updateFormFields(movementSelect.value || '{{ old('transfer_movement') }}');
        
        // Add change listeners to all form inputs
        const formInputs = document.querySelectorAll('input[type="checkbox"], select');
        formInputs.forEach(input => {
            input.addEventListener('change', notifyParentOfFormChange);
        });
        
        // Handle form submission
        const fetsForm = document.getElementById('fetsForm');
        if (fetsForm) {
            fetsForm.addEventListener('submit', function(e) {
                // Check if this is an edit/update request (embed mode)
                const editFetsId = document.querySelector('input[name="edit_fets_id"]');
                
                if (editFetsId && editFetsId.value) {
                    // This is an update - handle with AJAX to process JSON response
                    e.preventDefault();
                    
                    const formData = new FormData(fetsForm);
                    
                    fetch(fetsForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Notify parent window to refresh and show success message
                            if (window.parent !== window) {
                                window.parent.postMessage({
                                    type: 'fetsUpdated',
                                    message: data.message
                                }, '*');
                            }
                        } else {
                            alert(data.message || 'An error occurred while updating the FETS request.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the FETS request.');
                    });
                } else {
                    // This is a new submission - notify parent normally
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'formSaved'
                        }, '*');
                    }
                }
            });
        }
    });

    // Notify parent window of form changes
    function notifyParentOfFormChange() {
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'formChanged'
            }, '*');
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    if (document.getElementById('to_receiver')) {
        new TomSelect('#to_receiver', {
            create: false,
            placeholder: '-- Select Receiver --',
            maxOptions: 1000,
            sortField: { field: 'text', direction: 'asc' }
        });
    }
</script>
</x-app-layout>
