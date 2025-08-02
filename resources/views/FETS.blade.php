<x-app-layout>
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

            {{-- FILTER --}}
            <form method="GET" action="{{ route('fets.select') }}" class="mb-4 flex gap-4 flex-wrap">
                <input type="text" name="description" value="{{ request('description') }}"
                    placeholder="Filter by Description"
                    class="border rounded p-2 text-sm w-full sm:w-60" />

                    <select name="per_page" onchange="this.form.submit()" class="border rounded p-2 text-sm">
                        <option value="10" {{ (request('per_page') ?? session('per_page', 10)) == 10 ? 'selected' : '' }}>Show 10</option>
                        <option value="20" {{ (request('per_page') ?? session('per_page', 10)) == 20 ? 'selected' : '' }}>Show 20</option>
                        <option value="50" {{ (request('per_page') ?? session('per_page', 10)) == 50 ? 'selected' : '' }}>Show 50</option>
                        <option value="{{ $allEquipment->count() }}" {{ (request('per_page') ?? session('per_page', 10)) == $allEquipment->count() ? 'selected' : '' }}>Show All</option>
                    </select>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
                    Apply Filter
                </button>
                <a href="{{ route('fets.select') }}" class="text-sm text-gray-600 hover:underline">Reset</a>
            </form>

            {{-- FORM --}}
            <form action="{{ route('fets.generate') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">To Accountable Person</label>
                    <select id="to_receiver" name="to_receiver" required class="w-full border rounded p-2 text-sm">
                        <option value="">-- Select --</option>
                        @foreach ($receivers as $receiver)
                            <option value="{{ $receiver }}">{{ $receiver }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Remarks</label>
                    <select name="remarks" class="w-full border rounded p-2 text-sm" required>
                        <option value="Serviceable">Serviceable</option>
                        <option value="Unserviceable">Unserviceable</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Select Equipment (max 5)</label>
                    <table class="w-full table-auto text-sm border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2">✔</th>
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
                                            🔒 <span class="text-xs">FETS in Process</span>
                                        @else
                                            <input type="checkbox" name="selected[]" value="{{ $item->PROPERTY_NO }}"
                                                class="select-checkbox">
                                        @endif
                                    </td>
                                    <td class="p-2">{{ $item->PROPERTY_NO }}</td>
                                    <td class="p-2">{{ $item->GENERAL_DESCRIPTION }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center p-2">No equipment available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-2">{{ $inventory->links() }}</div>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    Submit FETS Request
                </button>
            </form>
        </div>
    </div>

    {{-- ✅ Limit checkbox logic --}}
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

{{-- ✅ TomSelect CDN + Init --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    new TomSelect('#to_receiver', {
        create: false,
        allowEmptyOption: true,
        placeholder: '-- Select Receiver --',
        maxOptions: 1000,
        sortField: {
            field: 'text',
            direction: 'asc'
        }
    });
</script>

</x-app-layout>
