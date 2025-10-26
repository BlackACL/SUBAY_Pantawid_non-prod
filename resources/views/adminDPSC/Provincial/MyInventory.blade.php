<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventory') }}
        </h2>
    </x-slot>

    <div class="h-[calc(100vh-4rem)] overflow-y-auto"
     x-data="{ showFetsApp: false, showSubmitted: false }"
     x-init="
        showFetsApp = false;
        showSubmitted = false;
     "
     x-effect="
        if (showFetsApp || showSubmitted) {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        } else {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        }
     ">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <!-- Inventory Table Section (same as before) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4" id="inventoryForm">
                            <label class="flex items-center">
                                <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }} onchange="resetPaginationAndSubmit()">
                                <span class="ml-2 text-sm">Show All Equipment</span>
                            </label>

                            <div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Search by Receiver, Description, Serial No, or Property No..."
                                    class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-96">
                            </div>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>

                            @if(request('search'))
                            <a href="{{ route(Route::currentRouteName()) }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                                Clear
                            </a>
                            @endif
                        </form>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button 
                                id="fetsButton"
                                class="bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded"
                            >
                                FETS Application
                            </button>
                        </div>
                    </div>

                    <!-- Fix: Make table horizontally scrollable -->
                    <div class="overflow-x-auto w-full">
                        <table class="table-fixed min-w-[1800px] w-full border text-sm" style="min-width: 1800px;">
                            <colgroup>
                                <col style="width: 64px;"> <!-- No. -->
                                <col style="width: 96px;"> <!-- FUND CODE -->
                                <col style="width: 128px;"> <!-- PROPERTY STATUS -->
                                <col style="width: 176px;"> <!-- ARTICLE DESCRIPTION -->
                                <col style="width: 208px;"> <!-- GENERAL DESCRIPTION -->
                                <col style="width: 128px;"> <!-- SERIAL NO -->
                                <col style="width: 128px;"> <!-- PROPERTY NO -->
                                <col style="width: 112px;"> <!-- PAR NO -->
                                <col style="width: 112px;"> <!-- PAR DATE -->
                                <col style="width: 80px;"> <!-- UNIT -->
                                <col style="width: 64px;"> <!-- QTY -->
                                <col style="width: 128px;"> <!-- ACQUISITION COST -->
                                <col style="width: 128px;"> <!-- ACQUISITION DATE -->
                                @if(request('show_all'))
                                <col style="width: 144px;"> <!-- RECEIVER -->
                                @endif
                                <col style="width: 112px;"> <!-- ACCOUNT CODE -->
                                <col style="width: 96px;"> <!-- WARRANTY -->
                            </colgroup>
                            <thead class="bg-[#2e3192] text-center">
                                <tr>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">No.</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">FUND CODE</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">PROPERTY STATUS</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">ARTICLE DESCRIPTION</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">GENERAL DESCRIPTION</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">SERIAL NO</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">PROPERTY NO</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">PAR NO</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">PAR DATE</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">UNIT</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">QTY</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">ACQUISITION COST</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">ACQUISITION DATE</th>
                                    @if(request('show_all'))
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">RECEIVER</th>
                                    @endif
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">ACCOUNT CODE</th>
                                    <th class="px-2 py-2 text-white border h-12 min-h-12">WARRANTY</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventory as $index => $item)
                                    <tr>
                                        <td class="border px-2 py-2 text-center break-words">{{ $inventory->firstItem() + $index }}</td>
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->FUND_CODE ?? 'N/A' }}">{{ $item->FUND_CODE ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->PROPERTY_STATUS ?? 'N/A' }}">{{ $item->PROPERTY_STATUS ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->ARTICLE_DESCRIPTION ?? 'N/A' }}">{{ $item->ARTICLE_DESCRIPTION ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->GENERAL_DESCRIPTION }}">{{ $item->GENERAL_DESCRIPTION }}</td>
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->SERIAL_NO }}">{{ $item->SERIAL_NO }}</td>
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->PROPERTY_NO }}">{{ $item->PROPERTY_NO }}</td>
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->PAR_NO }}">{{ $item->PAR_NO }}</td>
                                        <td class="border px-2 py-2 text-center break-words overflow-hidden">{{ $item->PAR_DATE }}</td>
                                        <td class="border px-2 py-2 text-center break-words overflow-hidden" title="{{ $item->UNIT ?? 'N/A' }}">{{ $item->UNIT ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 text-center break-words">{{ $item->QTY ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->ACQUISITION_COST }}">{{ $item->ACQUISITION_COST }}</td>
                                        <td class="border px-2 py-2 text-center break-words overflow-hidden">{{ $item->ACQUISITION_DATE }}</td>
                                        @if(request('show_all'))
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->RECEIVER ?? 'N/A' }}">{{ $item->RECEIVER ?? 'N/A' }}</td>
                                        @endif
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->ACCOUNT_CODE ?? 'N/A' }}">{{ $item->ACCOUNT_CODE ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->WARRANTY ?? 'N/A' }}">{{ $item->WARRANTY ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ request('show_all') ? '16' : '15' }}" class="text-center py-4">No data found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $inventory->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- FETS Application Modal -->
        <div
            id="fetsModal"
            class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        >
            <div class="bg-white rounded-lg w-11/12 max-w-6xl h-[90vh] flex flex-col shadow-lg">

                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">FETS Application</h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700 text-3xl font-bold w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">&times;</button>
                </div>

                <div class="flex-1 overflow-hidden">
                    <iframe id="fetsIframe" src="" class="w-full h-full border-none"></iframe>
                </div>
            </div>
        </div>


    </div>
</x-ProvincialAdmin-layout>

<script>
    const fetsButton = document.getElementById('fetsButton');
    const fetsModal = document.getElementById('fetsModal');
    const closeModal = document.getElementById('closeModal');
    const fetsIframe = document.getElementById('fetsIframe');

    const fetsRoute = "{{ route('fets.select.embed') }}";

    // FETS Application
    fetsButton.addEventListener('click', () => {
        fetsIframe.src = fetsRoute;
        fetsModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    });
    closeModal.addEventListener('click', () => {
        fetsModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        fetsIframe.src = "";
    });

    // Function to reset pagination when show_all checkbox changes
    function resetPaginationAndSubmit() {
        const form = document.getElementById('inventoryForm');
        const url = new URL(form.action);
        
        // Remove the page parameter to reset to page 1
        url.searchParams.delete('page');
        
        // Set the form action to the URL without page parameter
        form.action = url.toString();
        
        // Submit the form
        form.submit();
    }
</script>
