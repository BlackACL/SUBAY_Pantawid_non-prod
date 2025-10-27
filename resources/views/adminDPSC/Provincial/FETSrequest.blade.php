<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pending FETS Requests</h2>
    </x-slot>

    <div class="py-6 overflow-y-auto" style="max-height: calc(100vh - 4.7rem);" x-data="{ showModal: false, pdfUrl: '', showRejectModal: false, rejectId: null, rejectReason: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded relative" id="success-alert">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-green-700 font-medium flex-1">{{ session('success') }}</p>
                            <!-- Close Button - Positioned on the very right -->
                            <button onclick="closeAlert('success-alert')" 
                                class="ml-auto text-green-500 hover:text-green-700 focus:outline-none transition-colors duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-[#2e3192]">
                            <tr>
                                <th class="w-[6%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">No.</th>
                                <th class="w-[25%] px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Requested By</th>
                                <th class="w-[25%] px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Receiver</th>
                                <th class="w-[14%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Remarks</th>
                                <th class="w-[16%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date Requested</th>
                                <th class="w-[14%] px-2 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($documents ?? [] as $index => $doc)
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
                                <td class="w-[16%] px-2 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium">
                                            {{ $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->format('M d, Y') : 'N/A' }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $doc->created_at ? \Carbon\Carbon::parse($doc->created_at)->format('h:i A') : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="w-[14%] px-1 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex justify-center items-center space-x-2">
                                        @if($doc->status === 'submitted')
                                            <form action="{{ route('fets.verify', $doc->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                class="flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Verify
                                                </button>
                                            </form>

                                            <form action="{{ route('fets.reject', $doc->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" @click="showRejectModal = true; rejectId = {{ $doc->id }}"
                                                class="flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Reject
                                                </button>
                                            </form>
                                            @else
                                                <span class="text-sm text-gray-500 italic">Processed</span>
                                            @endif

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
                                <td colspan="6" class="px-6 py-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending FETS requests</h3>
                                    <p class="mt-1 text-sm text-gray-500">All pending FETS will appear here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Reject FETS Request</h2>
                            <form :action="'/FETSrequest/' + rejectId + '/reject'" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="block text-sm text-gray-600 mb-1">Reason for rejection</label>
                                <textarea name="remarks" x-model="rejectReason" class="w-full border border-gray-300 rounded-md p-2 mb-4" rows="4" required></textarea>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" @click="showRejectModal = false; rejectReason = ''"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancel</button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
                @endif
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
</x-ProvincialAdmin-layout>
<script>
    // Function to close alert messages
    function closeAlert(alertId) {
        const alertElement = document.getElementById(alertId);
        if (alertElement) {
            alertElement.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                alertElement.remove();
            }, 300);
        }
    }

    // Auto-hide success alert after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[id$="-alert"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert) {
                    closeAlert(alert.id);
                }
            }, 5000); // 5 seconds
        });
    });
</script>