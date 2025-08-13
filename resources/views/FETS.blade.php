<x-app-layout>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('FETS') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm rounded-lg">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative transition duration-500 ease-in-out mb-4">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>

                    @if(session('fets_id'))
                        <div class="mt-3 flex gap-3">
                            <a href="{{ route('fets.download', ['id' => session('fets_id')]) }}"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition duration-200">
                                📥 Download PDF
                            </a>

                            <a href="{{ route('fets.preview', ['id' => session('fets_id')]) }}"
                            target="_blank"
                            class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow transition duration-200">
                                👁️ Preview PDF
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ✅ Show session error --}}
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ Show validation errors --}}
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 p-3 mb-4 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

            <form method="GET" action="{{ route('fets.select') }}" class="mb-4 flex gap-4 flex-wrap" id="filterForm">
                {{-- FILTER - moved here --}}
                <div class="mb-4 flex gap-4 flex-wrap">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by Description, Serial No, or Property No..."
                        class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-96">

                    <input type="hidden" name="to_receiver" id="hidden_receiver">

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>

                    @if(request('search'))
                    <a href="{{ route('fets.select') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
            <script>
                document.getElementById('filterForm').addEventListener('submit', function(e) {
                    const receiver = document.getElementById('to_receiver').value.trim();
                    if (!receiver) {
                        e.preventDefault();
                        alert('Please select an Accountable Person before searching.');
                    }
                });
            </script>

            {{-- FORM --}}
            <form action="{{ route('fets.generate') }}" method="POST">
                @csrf

                {{-- Receiver --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">To Accountable Person</label>
                    <select id="to_receiver" name="to_receiver" required class="w-full border rounded p-2 text-sm">
                        <option value="" disabled selected>-- Select Receiver --</option>
                        @foreach ($receivers as $receiver)
                            <option value="{{ $receiver }}" {{ request('to_receiver') == $receiver ? 'selected' : '' }}>
                                {{ $receiver }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Remarks --}}
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Remarks</label>
                    <select name="remarks" class="w-full border rounded p-2 text-sm" required>
                        <option value="Serviceable">Serviceable</option>
                        <option value="Unserviceable">Unserviceable</option>
                    </select>
                </div>
        
            {{-- FETS SUBMISSION FORM --}}
                {{-- Inventory Table --}}
                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700">Select Equipment (max 5)</label>
                        <table class="w-full table-auto text-sm border">
                            <thead class="bg-gray-100">
                                <tr>
                                    {{-- Show All dropdown now bound to filterForm (GET) --}}
                                    <th class="p-2">
                                        <select name="per_page"
                                                form="filterForm"
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
                                    @php
                                        $disabled = in_array($item->PROPERTY_NO, $inProcessPropertyNos ?? []);
                                    @endphp
                                    <tr class="{{ $disabled ? 'bg-gray-100 text-gray-500 italic' : 'transition duration-150 ease-in-out' }}">
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
                    {{--    <div class="mt-2">{{ $inventory->links() }}</div>   --}}
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
                const isDisabledDueToLimit = !cb.checked && checkedCount >= 5;

                cb.disabled = isDisabledDueToLimit;

                if (isDisabledDueToLimit) {
                    row.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    row.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        document.querySelectorAll('input.select-checkbox').forEach(cb => {
            cb.addEventListener('change', updateCheckboxState);
        });

        document.addEventListener('DOMContentLoaded', updateCheckboxState);
    </script>

    {{-- TomSelect --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        new TomSelect('#to_receiver', {
        create: false,
        placeholder: '-- Select Receiver --',
        maxOptions: 1000,
        sortField: { field: 'text', direction: 'asc' }
    });
    </script>

    <script>
        function updateHiddenReceiver() {
            document.getElementById('hidden_receiver').value = document.getElementById('to_receiver').value;
        }

        // Always update hidden field when submitting filterForm
        document.getElementById('filterForm').addEventListener('submit', updateHiddenReceiver);

        // Handle per_page dropdown changes
        document.querySelector('[name="per_page"]').addEventListener('change', function() {
            updateHiddenReceiver();
            document.getElementById('filterForm').submit();
        });
    </script>

</x-app-layout>



