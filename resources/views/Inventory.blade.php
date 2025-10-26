<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Inventory') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                <div class="p-6 text-gray-900">
                    <!-- FILTER FORM -->
                    <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4">
                        <div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search by Description, Serial No, or Property No..."
                                class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-96">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>

                        @if(request('search'))
                            <a href="{{ route(Route::currentRouteName()) }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                                Clear
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="table-fixed w-full border text-sm" style="min-width: 1800px;">
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
                                        <td class="border px-2 py-2 break-all overflow-hidden" title="{{ $item->ACCOUNT_CODE ?? 'N/A' }}">{{ $item->ACCOUNT_CODE ?? 'N/A' }}</td>
                                        <td class="border px-2 py-2 break-words overflow-hidden" title="{{ $item->WARRANTY ?? 'N/A' }}">{{ $item->WARRANTY ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="15" class="text-center py-4">No data found.</td></tr>
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
    </div>
</x-app-layout>