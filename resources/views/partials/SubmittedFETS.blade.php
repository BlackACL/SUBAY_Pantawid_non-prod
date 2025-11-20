<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submitted FETS Request') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ 
        showModal: false, 
        pdfUrl: '', 
        showEditModal: false, 
        editFetsId: null,
        hasUnsavedChanges: false,
        showDiscardModal: false,
        init() {
            // Set up global reference for iframe communication
            window.fetsModalComponent = this;
        },
        editFets(fetsId, transferMovement, remarks, repairDestination, propertyNos) {
            const params = new URLSearchParams({
                edit: fetsId,
                transfer_movement: transferMovement || '',
                remarks: remarks || '',
                repair_destination: repairDestination || '',
                selected_items: propertyNos || ''
            });
            
            const editUrl = '{{ route('fets.select.embed') }}?' + params.toString();
            document.getElementById('editFetsIframe').src = editUrl;
            this.showEditModal = true;
            this.editFetsId = fetsId;
            this.hasUnsavedChanges = false;
        },
        closeEditModal() {
            console.log('=== CLOSE MODAL CALLED ===');
            console.log('hasUnsavedChanges:', this.hasUnsavedChanges);
            console.log('window.formHasUnsavedChanges:', window.formHasUnsavedChanges);
            
            if (this.hasUnsavedChanges || window.formHasUnsavedChanges) {
                console.log('>>> SHOWING DISCARD MODAL');
                this.showDiscardModal = true;
            } else {
                console.log('>>> CLOSING DIRECTLY');
                this.showEditModal = false;
                document.getElementById('editFetsIframe').src = 'about:blank';
            }
        },
        discardChanges() {
            this.showEditModal = false;
            this.showDiscardModal = false;
            this.hasUnsavedChanges = false;
            window.formHasUnsavedChanges = false;
            document.getElementById('editFetsIframe').src = 'about:blank';
        },
        cancelDiscard() {
            this.showDiscardModal = false;
        }
    }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#2e3192]">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">No.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">FETS ID</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Receiver</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Items</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($documents as $index => $doc)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900">
                                {{ $documents->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-center">
                                {{ $doc->created_at->format('Ymd') }}-{{ $doc->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center">
                                {{ $doc->to_receiver }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-center">
                                {{ count(explode(',', $doc->property_no)) }}
                            </td>
                            <td class="px-6 py-4 align-middle text-center">
                                @php
                                    $status = $doc->status;
                                    $statusDisplay = '';
                                    $statusClasses = '';
                                    $statusDate = '';
                                    
                                    switch($status) {
                                        case 'submitted':
                                            $statusDisplay = 'Pending';
                                            $statusClasses = 'bg-yellow-100 text-yellow-800';
                                            $statusDate = 'Submitted on ' . $doc->created_at->format('M d, Y');
                                            break;
                                        case 'verified':
                                            $statusDisplay = 'Verified';
                                            $statusClasses = 'bg-purple-100 text-purple-800';
                                            $statusDate = 'Verified on ' . ($doc->verified_at ? \Carbon\Carbon::parse($doc->verified_at)->format('M d, Y') : $doc->updated_at->format('M d, Y'));
                                            break;
                                        case 'approved':
                                            $statusDisplay = 'Approved';
                                            $statusClasses = 'bg-green-100 text-green-800';
                                            $statusDate = 'Approved on ' . ($doc->approved_at ? \Carbon\Carbon::parse($doc->approved_at)->format('M d, Y') : $doc->updated_at->format('M d, Y'));
                                            break;
                                        case 'rejected':
                                            $statusDisplay = 'Rejected';
                                            $statusClasses = 'bg-red-100 text-red-800';
                                            $statusDate = 'Rejected on ' . ($doc->rejected_at ? \Carbon\Carbon::parse($doc->rejected_at)->format('M d, Y') : $doc->updated_at->format('M d, Y'));
                                            break;
                                        default:
                                            $statusDisplay = ucfirst($status);
                                            $statusClasses = 'bg-gray-100 text-gray-800';
                                            $statusDate = '';
                                    }
                                @endphp

                                <div class="text-center">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClasses }}">
                                        {{ $statusDisplay }}
                                    </span>
                                    
                                    @if($statusDate)
                                        <div class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">
                                            {{ $statusDate }}
                                        </div>
                                    @endif

                                    @if($doc->status === 'rejected' && $doc->rejected_remarks)
                                        <div class="mt-1">
                                            <button 
                                                class="text-red-600 underline hover:text-red-800 font-medium text-xs"
                                                @click="$dispatch('open-remarks', { remarks: '{{ addslashes($doc->rejected_remarks) }}' })">
                                                View Rejection Reason
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex justify-center items-center space-x-2 h-full">
                                    <!-- Edit Button (only for pending FETS) -->
                                    @if($doc->status === 'submitted')
                                    @php
                                        $propertyNos = DB::table('fets_items')->where('fets_document_id', $doc->id)->pluck('property_no')->implode(',');
                                    @endphp
                                    <button @click="editFets({{ $doc->id }}, '{{ addslashes($doc->transfer_movement) }}', '{{ addslashes($doc->remarks) }}', '{{ addslashes($doc->repair_destination) }}', '{{ $propertyNos }}')"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    @endif

                                    <!-- Download Button -->
                                    <a href="{{ route('fets.download', $doc->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-700">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Download
                                    </a>

                                    <!-- Preview Button -->
                                    <button @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-[#eee201] hover:bg-[#fef200] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#fef200]">
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
                            <td colspan="6" class="px-6 py-4 text-center">
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No submitted FETS</h3>
                                <p class="mt-1 text-sm text-gray-500">Your submitted FETS requests will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($documents->hasPages())
            <div class="mt-4 p-6">
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

    <!-- Remarks Modal -->
    <div 
        x-data="{ showRemarks: false, remarks: '' }"
        x-on:open-remarks.window="showRemarks = true; remarks = $event.detail.remarks"
        x-show="showRemarks"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        style="display: none;">
        
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
            <h3 class="text-lg font-semibold text-red-600 mb-4">Rejection Reason</h3>
            <p class="text-gray-700 text-sm whitespace-pre-line" x-text="remarks"></p>
            
            <div class="mt-6 flex justify-end">
                <button class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                        @click="showRemarks = false">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit FETS Modal -->
    <div 
        x-show="showEditModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        style="display: none;">
        
        <div class="bg-white rounded-lg w-11/12 max-w-6xl h-[90vh] flex flex-col shadow-lg">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold">Edit FETS Request</h3>
                <button @click="closeEditModal()" class="text-gray-500 hover:text-gray-700 text-3xl font-bold w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">&times;</button>
            </div>
            
            <div class="flex-1 overflow-hidden">
                <iframe id="editFetsIframe" src="" class="w-full h-full border-none"></iframe>
            </div>
        </div>
    </div>

    <!-- Discard Changes Modal -->
    <div 
        x-show="showDiscardModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50"
        style="display: none;">
        <div class="fixed inset-0 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Discard changes?</h3>
                    <p class="text-gray-600 mb-6">You have unsaved changes. Are you sure you want to discard them?</p>
                    <div class="flex justify-center gap-4">
                        <button @click="discardChanges()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Yes</button>
                        <button @click="cancelDiscard()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
                // Global variable to track unsaved changes
        window.formHasUnsavedChanges = false;
        
        // Listen for messages from iframe
        window.addEventListener('message', function(event) {
            console.log('📨 Message received:', event.data);
            
            if (event.data.type === 'fetsUpdated') {
                // Store success message in sessionStorage
                if (event.data.message) {
                    sessionStorage.setItem('fetsUpdateSuccess', event.data.message);
                }
                
                // Refresh the page to show updated data
                window.location.reload();
            }
            
            // Listen for form changes in the iframe
            if (event.data.type === 'formChanged') {
                console.log('🔄 Form changed detected!');
                window.formHasUnsavedChanges = true;
                
                // Update Alpine component if available
                if (window.fetsModalComponent) {
                    window.fetsModalComponent.hasUnsavedChanges = true;
                    console.log('✅ Alpine component updated');
                }
            }
            
            // Listen for form saved/reset in the iframe
            if (event.data.type === 'formSaved' || event.data.type === 'formReset') {
                console.log('💾 Form saved/reset detected');
                window.formHasUnsavedChanges = false;
                
                // Update Alpine component if available
                if (window.fetsModalComponent) {
                    window.fetsModalComponent.hasUnsavedChanges = false;
                }
            }
        });

        // Show success message if it exists in sessionStorage
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = sessionStorage.getItem('fetsUpdateSuccess');
            if (successMessage) {
                // Remove from storage
                sessionStorage.removeItem('fetsUpdateSuccess');
                
                // Hide any existing Laravel session success messages
                const existingAlerts = document.querySelectorAll('.bg-green-100, .bg-green-50');
                existingAlerts.forEach(alert => {
                    if (alert.textContent.includes('FETS') || alert.textContent.includes('PDF')) {
                        alert.remove();
                    }
                });
                
                // Create and show success alert
                const alertHtml = `
                    <div id="success-alert" class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-green-700 font-medium">${successMessage}</p>
                            </div>
                            <button onclick="closeAlert('success-alert')" 
                                class="text-green-500 hover:text-green-700 focus:outline-none transition-colors duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                // Insert at the top of the content area
                const container = document.querySelector('.bg-white.overflow-hidden.shadow-sm.sm\\:rounded-lg, .bg-white.shadow-lg.rounded-lg');
                if (container) {
                    container.insertAdjacentHTML('afterbegin', alertHtml);
                }
            }
        });

        // Function to close alert
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }
    </script>
</div>
</x-app-layout>