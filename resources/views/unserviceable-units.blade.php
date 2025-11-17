<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Unserviceable Units') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        
        {{-- Alert Message --}}
        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
            <p class="font-bold">⚠️ Unserviceable Equipment</p>
            <p>These items are marked as unserviceable and cannot be transferred via FETS. They are locked for disposal or return to supplier.</p>
        </div>

        {{-- Search Bar --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('unserviceable.units') }}" class="flex gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by Description, Serial No, or Property No..." 
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                @if(request('search'))
                    <a href="{{ route('unserviceable.units') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Inventory Table --}}
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#2e3192]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fund Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Property Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Article Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">General Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Serial No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Property No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">PAR No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">PAR Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Submitted By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($inventory as $index => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $inventory->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->FUND_CODE ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        {{ $item->STATUS ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->ARTICLE_DESCRIPTION ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->GENERAL_DESCRIPTION ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->SERIAL_NO ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-blue-600">
                                    {{ $item->PROPERTY_NO ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->PAR_NO ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->PAR_DATE ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->submitted_by ?? 'Unknown' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $item->DPO_REMARKS ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                                    No unserviceable units found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $inventory->links() }}
            </div>
        </div>

        {{-- Summary --}}
        <div class="mt-4 p-4 bg-gray-100 rounded-lg">
            <p class="text-sm text-gray-700">
                <strong>Total Unserviceable Units:</strong> {{ $inventory->total() }}
            </p>
        </div>
    </div>
</x-app-layout>
