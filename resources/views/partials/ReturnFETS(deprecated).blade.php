@extends('layouts.modal')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <div class="p-6">
        <div class="bg-white p-6 shadow-sm rounded-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Return from Repair FETS</h2>
            <p class="text-gray-600 mb-4">
                Only units submitted by the same employee can be selected together. Units will show after being approved for repair.
            </p>

            {{-- ✅ Success --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- ✅ Errors --}}
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 mb-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if($returnableUnits->isEmpty())
                <div class="text-gray-600 italic">No units available for return at the moment.</div>
            @else
                <form action="{{ route('fets.return.submit') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700 mb-2">
                            Select Units to Return (max 5)
                        </label>
                        <table class="w-full table-auto text-sm border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-2">Select</th>
                                    <th class="p-2">Property No</th>
                                    <th class="p-2">Description</th>
                                    <th class="p-2">Original Owner</th>
                                    <th class="p-2">Submitted FETS</th>
                                    <th class="p-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnableUnits as $unit)
                                    @php
                                        $isLocked    = $unit->STATUS === 'Pending Return';
                                        $isRestored  = $unit->STATUS === 'Operational'
                                                       && str_contains($unit->DPO_REMARKS ?? '', 'Returned from Repair');
                                        $fets = $fetsForRepair->firstWhere(function($f) use ($unit) {
                                            return in_array($unit->PROPERTY_NO, explode(',', $f->property_no));
                                        });
                                        $originalSubmitter = $unitSubmitters[$unit->PROPERTY_NO] ?? 'Unknown';
                                    @endphp
                                    <tr class="{{ ($isLocked || $isRestored) ? 'bg-gray-100 text-gray-500 italic' : '' }}">
                                        <td class="p-2 text-center">
                                            @if ($isLocked)
                                                <span class="text-xs">FETS in Process</span>
                                            @elseif ($isRestored)
                                                <span class="text-xs text-green-600 font-semibold">Returned & Restored</span>
                                            @else
                                                <input type="checkbox"
                                                       name="selected[]"
                                                       value="{{ $unit->PROPERTY_NO }}"
                                                       class="select-checkbox"
                                                       data-submitter="{{ $originalSubmitter }}">
                                            @endif
                                        </td>
                                        <td class="p-2 text-center">{{ $unit->PROPERTY_NO }}</td>
                                        <td class="p-2 text-center">{{ $unit->GENERAL_DESCRIPTION }}</td>
                                        <td class="p-2 text-center">{{ $originalSubmitter }}</td>
                                        <td class="p-2 text-center">{{ $fets ? $fets->id : 'N/A' }}</td>
                                        <td class="p-2 text-center">{{ $unit->STATUS ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                        Submit Return
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ✅ Checkbox limit & enforce single employee selection --}}
    <script>
        function updateCheckboxState() {
            const checkboxes = Array.from(document.querySelectorAll('input.select-checkbox'));
            const checkedBoxes = checkboxes.filter(cb => cb.checked);

            const selectedSubmitter = checkedBoxes[0]?.dataset?.submitter || null;

            checkboxes.forEach(cb => {
                const row = cb.closest('tr');

                const disabledBySubmitter = selectedSubmitter && cb.dataset.submitter !== selectedSubmitter;
                const disabledByLimit = !cb.checked && checkedBoxes.length >= 5;

                cb.disabled = disabledBySubmitter || disabledByLimit;

                row.classList.toggle('opacity-50', disabledBySubmitter || (disabledByLimit && !cb.checked));
                row.classList.toggle('cursor-not-allowed', disabledBySubmitter || (disabledByLimit && !cb.checked));
                row.classList.toggle('bg-yellow-50', !disabledBySubmitter && !cb.checked && !cb.disabled);

                if(disabledBySubmitter) {
                    cb.title = "Cannot select units from other employees";
                } else {
                    cb.title = "";
                }
            });
        }

        document.querySelectorAll('input.select-checkbox').forEach(cb =>
            cb.addEventListener('change', updateCheckboxState)
        );

        document.addEventListener('DOMContentLoaded', updateCheckboxState);
    </script>
@endsection
