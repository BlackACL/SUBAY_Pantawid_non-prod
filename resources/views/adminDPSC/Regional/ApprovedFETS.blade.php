<x-RegionalAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Verified FETS Requests
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ showModal: false, pdfUrl: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded p-6">
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

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#2e3192]">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">FETS No</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">To Receiver</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Remarks</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($documents as $doc)
                            <tr>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                                    {{ $doc->fets_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                    {{ $doc->to_receiver }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600 max-w-xs truncate">
                                    {{ $doc->remarks ?? 'None' }}
                                </td>
                                <td class="flex-col justify-center items-center text-center px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block px-3 py-1 text-xs leading-5 font-semibold rounded-full 
                                        @if($doc->status === 'verified') bg-purple-100 text-purple-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($doc->updated_at)->format('M d, Y h:i A') }}
                                    </div>
                                </td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex justify-center items-center space-x-2">
                                        <button @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                            class="flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
                                <td colspan="5" class="text-center py-4 text-gray-600">No verified FETS documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
            x-show="showModal"
            style="display: none;"
        >
            <div class="bg-white rounded-lg overflow-hidden w-11/12 max-w-4xl h-[90vh] relative">
                <div class="flex justify-between items-center bg-gray-100 px-4 py-2">
                    <h3 class="text-lg font-semibold">FETS Preview</h3>
                    <button class="text-gray-600 hover:text-black text-2xl leading-none" @click="showModal = false">&times;</button>
                </div>
                <iframe
                    :src="pdfUrl"
                    class="w-full h-full"
                    frameborder="0"
                ></iframe>
            </div>
        </div>
    </div>
</x-RegionalAdmin-layout>
