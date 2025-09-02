<x-app-layout>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('FETS') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm rounded-lg">

            {{-- ✅ Success --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
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
            @endif

            {{-- ✅ Errors --}}
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">
                    {{ session('error') }}
                </div>
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

            {{-- FILTER --}}
            <form method="GET" action="{{ route('fets.select') }}" class="mb-4 flex gap-4 flex-wrap" id="filterForm">
                <div class="mb-4 flex gap-4 flex-wrap">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by Description, or Property No..."
                           class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-72">

                    <input type="hidden" name="to_receiver" id="hidden_receiver">

                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        🔍
                    </button>

                    @if(request('search'))
                        <a href="{{ route('fets.select', ['to_receiver' => request('to_receiver')]) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            {{-- FORM --}}
            <form action="{{ route('fets.generate') }}" method="POST">
                @csrf

                {{-- Transfer Movement --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Transfer Movement</label>
                    <select id="transfer_movement" name="transfer_movement" required
                            class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('transfer_movement') ? '' : 'selected' }}>
                            -- Select Transfer Movement --
                        </option>
                        <option value="Return to Lender" {{ old('transfer_movement') == 'Return to Lender' ? 'selected' : '' }}>
                            Return to Lender
                        </option>
                        <option value="For Surrender" {{ old('transfer_movement') == 'For Surrender' ? 'selected' : '' }}>
                            For Surrender
                        </option>
                        <option value="For Repair" {{ old('transfer_movement') == 'For Repair' ? 'selected' : '' }}>
                            For Repair
                        </option>
                    </select>
                </div>

                {{-- Repair Destination (conditional) --}}
                <div id="repair_destination_wrapper"
                     class="mb-4 {{ old('transfer_movement') == 'For Repair' ? '' : 'hidden' }}">
                    <label class="block font-medium text-sm text-gray-700">Repair Destination</label>
                    <select id="repair_destination" name="repair_destination" class="w-full border rounded p-2 text-sm">
                        <option value="" disabled {{ old('repair_destination') ? '' : 'selected' }}>
                            -- Select Repair Destination --
                        </option>
                        @foreach($repairDestinations as $dest)
                            <option value="{{ $dest->name }}" {{ old('repair_destination') == $dest->name ? 'selected' : '' }}>
                                {{ $dest->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Receiver (auto-filled) --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">To Accountable Person</label>
                    <input type="text" id="to_receiver_display"
                           value="{{ old('to_receiver_display', '(auto-filled based on Transfer Movement + Remarks)') }}"
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

                {{-- Inventory Table --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Select Equipment (max 5)</label>
                    <table class="w-full table-auto text-sm border">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2">
                                <select name="per_page" form="filterForm"
                                        class="border rounded p-1 text-sm w-24">
                                    <option value="10" {{ (request('per_page') ?? session('per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                                    <option value="20" {{ (request('per_page') ?? session('per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                                    <option value="50" {{ (request('per_page') ?? session('per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                                    <option value="{{ $allEquipment->count() }}" {{ (request('per_page') ?? session('per_page', 10)) == $allEquipment->count() ? 'selected' : '' }}>Show All</option>
                                </select>
                            </th>
                            <th class="p-2">Property No</th>
                            <th class="p-2">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($inventory as $item)
                            @php $disabled = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []); @endphp
                            <tr class="{{ $disabled ? 'bg-gray-100 text-gray-500 italic' : '' }}">
                                <td class="p-2 text-center">
                                    @if ($disabled)
                                        <span class="text-xs">FETS in Process</span>
                                    @else
                                        <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}"
                                               class="select-checkbox">
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
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    Submit FETS Request
                </button>
            </form>
        </div>
    </div>

    {{-- Checkbox limit script --}}
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
    </script>

    {{-- Transfer Movement Logic --}}
    <script>
        const movementSelect = document.getElementById('transfer_movement');
        const remarksSelect = document.getElementById('remarks');
        const remarksWrapper = document.getElementById('remarks_wrapper');
        const repairWrapper = document.getElementById('repair_destination_wrapper');
        const repairSelect = document.getElementById('repair_destination');
        const toReceiverHidden = document.getElementById('to_receiver');
        const toReceiverDisplay = document.getElementById('to_receiver_display');

        function updateReceiver() {
            const movement = movementSelect.value;
            const remarks = remarksSelect.value;

            if (movement === 'For Repair') {
                repairWrapper.classList.remove('hidden');
                remarksWrapper.classList.add('hidden');
                if (repairSelect.value) {
                    toReceiverHidden.value = repairSelect.value;
                    toReceiverDisplay.value = repairSelect.value;
                } else {
                    toReceiverHidden.value = '';
                    toReceiverDisplay.value = '(select repair destination)';
                }
            } else {
                repairWrapper.classList.add('hidden');
                remarksWrapper.classList.remove('hidden');
                if ((movement === 'Return to Lender' || movement === 'For Surrender') && remarks) {
                    if (remarks === 'Serviceable') {
                        toReceiverHidden.value = '(auto-fill Provincial DPSC via backend)';
                        toReceiverDisplay.value = 'Provincial DPSC (auto-filled)';
                    } else if (remarks === 'Unserviceable') {
                        toReceiverHidden.value = 'Al Jay Meliton';
                        toReceiverDisplay.value = 'Al Jay Meliton (Head of Property)';
                    }
                } else {
                    toReceiverHidden.value = '';
                    toReceiverDisplay.value = '(auto-filled based on Transfer Movement + Remarks)';
                }
            }
        }

        movementSelect.addEventListener('change', updateReceiver);
        remarksSelect.addEventListener('change', updateReceiver);
        repairSelect.addEventListener('change', function () {
            toReceiverHidden.value = this.value;
            toReceiverDisplay.value = this.value;
        });

        document.addEventListener('DOMContentLoaded', updateReceiver);
    </script>

</x-app-layout>
