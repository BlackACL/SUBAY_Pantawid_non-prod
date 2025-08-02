<x-RegionalAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Verified FETS Requests
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ showModal: false, pdfUrl: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded p-4">
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

                <table class="table-auto w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border">FETS No</th>
                            <th class="px-4 py-2 border">To Receiver</th>
                            <th class="px-4 py-2 border">Remarks</th>
                            <th class="px-4 py-2 border">Status / Timestamp</th>
                            <th class="px-4 py-2 border text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $doc)
                            <tr>
                                <td class="px-4 py-2 border">{{ $doc->fets_no }}</td>
                                <td class="px-4 py-2 border">{{ $doc->to_receiver }}</td>
                                <td class="px-4 py-2 border">{{ $doc->remarks ?? 'None' }}</td>
                                <td class="px-4 py-2 border text-sm text-gray-700">
                                    <div class="mb-1">
                                        <span class="px-2 py-1 rounded bg-green-200 text-xs text-green-800 font-semibold">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($doc->updated_at)->format('M d, Y h:i A') }}
                                    </div>
                                </td>
                                <td class="px-4 py-2 border text-center">
                                    <button
                                        @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium"
                                    >
                                        Preview
                                    </button>
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
