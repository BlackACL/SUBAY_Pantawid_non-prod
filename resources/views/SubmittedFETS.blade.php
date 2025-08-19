<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Submitted FETS') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, pdfUrl: '' }">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FETS No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Receiver</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verification</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($documents as $doc)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $doc->fets_no }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $doc->to_receiver }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ count(explode(',', $doc->property_no)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'submitted' => 'bg-blue-100 text-blue-800',
                                                'verified' => 'bg-purple-100 text-purple-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$doc->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($doc->status) }}
                                        </span>

                                        @if($doc->status === 'rejected' && $doc->rejected_remarks)
                                            <div class="mt-1 text-xs">
                                                <button 
                                                    class="text-red-600 underline hover:text-red-800 font-medium"
                                                    @click="$dispatch('open-remarks', { remarks: '{{ addslashes($doc->rejected_remarks) }}' })">
                                                    View More
                                                </button>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($doc->verified_by)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Verified
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                By: {{ $doc->verifier->fullname ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $doc->updated_at->format('M d, Y') }}
                                            </div>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($doc->approved_by)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                By: {{ $doc->approver->fullname ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $doc->updated_at->format('M d, Y') }}
                                            </div>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('fets.download', $doc->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">Download</a>
                                        <button @click="pdfUrl = '{{ route('fets.preview', $doc->id) }}'; showModal = true"
                                           class="text-indigo-600 hover:text-indigo-900">Preview</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No submitted FETS</h3>
                                        <p class="mt-1 text-sm text-gray-500">Your submitted FETS requests will appear here.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($documents->hasPages())
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
             x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div class="bg-white rounded-lg overflow-hidden w-11/12 max-w-4xl h-[90vh] relative">
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
            style="display: none;"
            x-transition>
            
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Rejection Remarks</h3>
                <p class="text-gray-700 text-sm whitespace-pre-line" x-text="remarks"></p>
                
                <div class="mt-6 flex justify-end">
                    <button 
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
                        @click="showRemarks = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>