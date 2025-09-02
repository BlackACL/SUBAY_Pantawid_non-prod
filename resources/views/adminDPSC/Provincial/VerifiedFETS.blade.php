<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Verified FETS Requests</h2>
    </x-slot>

    <div class="py-6" x-data="{ showModal: false, pdfUrl: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                <form method="GET" action="{{ route('Provincial.VerifiedFETS') }}" class="mb-4 flex gap-2 items-center">

    <label for="date_filter" class="text-sm text-gray-700">Filter by:</label>
    <select name="date_filter" id="date_filter" onchange="this.form.submit()"
        class="border border-gray-300 rounded px-3 py-1 text-sm w-auto min-w-[110px] pr-6">
        <option value="">All</option>
        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
        <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
        <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
    </select>
</form>


                <div class="overflow-x-auto">
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
                                <td class="px-6 py-4 text-sm text-center font-medium text-gray-900">{{ $doc->fets_no }}</td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $doc->to_receiver }}</td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600">{{ $doc->remarks ?? 'None' }}</td>
                                <td class="flex justify-center items-center px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <button @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Preview
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No verified FETS documents.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- PDF Modal -->
        <div class="fixed inset-0 z-50 overflow-y-auto" x-show="showModal" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full text-left">
                                <div class="flex justify-between items-center border-b pb-3">
                                    <h3 class="text-lg font-medium text-gray-900">FETS Document Preview</h3>
                                </div>
                                <div class="mt-4">
                                    <iframe :src="pdfUrl" class="w-full h-[70vh] border rounded-md"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Close Preview
                        </button>
                        <a :href="pdfUrl" download target="_blank"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ProvincialAdmin-layout>
