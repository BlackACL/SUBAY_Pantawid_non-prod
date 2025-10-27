<x-RegionalAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approved FETS Requests
        </h2>
    </x-slot>

    <div class="py-6 overflow-y-auto" style="max-height: calc(100vh - 4.7rem);" x-data="{ showModal: false, pdfUrl: '' }">
        <div class="w-full px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg p-6 w-full">
                @if(session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif

                <form method="GET" action="{{ route('Regional.ApprovedFETS') }}" class="mb-4 flex gap-2 items-center">
                    <label for="date_filter" class="text-sm text-gray-700">Filter by:</label>
                    <select name="date_filter" id="date_filter" onchange="this.form.submit()"
                        class="border border-gray-300 rounded px-3 py-1 text-sm">
                        <option value="">All</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-[#2e3192]">
                            <tr>
                                <th class="w-[6%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">No.</th>
                                <th class="w-[25%] px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Requested By</th>
                                <th class="w-[25%] px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Receiver</th>
                                <th class="w-[14%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Remarks</th>
                                <th class="w-[16%] px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date Approved</th>
                                <th class="w-[14%] px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $index => $doc)
                                <tr>
                                    <td class="w-[6%] px-2 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                                    {{ $documents->firstItem() + $index }}
                                    </td>
                                    <td class="w-[25%] px-3 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        <div class="truncate">{{ $doc->submitter->fullname ?? 'Unknown' }}</div>
                                    </td>
                                    <td class="w-[25%] px-3 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        <div class="truncate">{{ $doc->to_receiver }}</div>
                                    </td>
                                    <td class="w-[14%] px-2 py-4 text-sm text-gray-600 text-center max-w-xs truncate">
                                        <div class="truncate">{{ $doc->remarks ?? 'None' }}</div>
                                    </td>
                                    <td class="w-[16%] px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                        <div class="flex flex-col items-center">
                                            <span class="font-medium">
                                                {{ $doc->approved_at ? \Carbon\Carbon::parse($doc->approved_at)->format('M d, Y') : \Carbon\Carbon::parse($doc->updated_at)->format('M d, Y') }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $doc->approved_at ? \Carbon\Carbon::parse($doc->approved_at)->format('h:i A') : \Carbon\Carbon::parse($doc->updated_at)->format('h:i A') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="w-[14%] px-2 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-2">
                                            <button @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                                class="flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-[#eee201] hover:bg-[#fef200] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#fef200]">
                                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Preview
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-600">No approved FETS documents found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50" 
            x-show="showModal"
            style="display: none;">
            <div class="bg-white rounded-lg overflow-hidden w-11/12 max-w-full h-[90vh] relative">
                <div class="flex justify-between items-center bg-gray-100 px-4 py-2">
                    <h3 class="text-lg font-semibold">FETS Preview</h3>
                    <button class="text-gray-600 hover:text-gray-800 text-2xl" @click="showModal = false">&times;</button>
                </div>
                <iframe :src="pdfUrl" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</x-RegionalAdmin-layout>