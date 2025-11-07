<div class="bg-white shadow rounded-xl p-6">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">
        <i class="fas fa-book text-blue-600 mr-2"></i>User Manual Management
    </h2>

    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
        <p class="text-sm text-gray-700">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Note:</strong> Upload a PDF file to serve as the user manual. This manual will be accessible to all users via the "User Manual" button in the sidebar. Maximum file size: 100MB.
        </p>
    </div>

    {{-- Current Manual Status --}}
    <div class="mb-6 p-4 border rounded-lg {{ $currentManual ? 'bg-green-50 border-green-300' : 'bg-yellow-50 border-yellow-300' }}">
        <h3 class="font-semibold text-lg mb-2 flex items-center">
            <i class="fas {{ $currentManual ? 'fa-check-circle text-green-600' : 'fa-exclamation-triangle text-yellow-600' }} mr-2"></i>
            Current Manual Status
        </h3>
        
        @if($currentManual)
            <div class="space-y-2">
                <p class="text-sm"><strong>File:</strong> {{ $currentManual->original_name }}</p>
                <p class="text-sm"><strong>Size:</strong> {{ number_format($currentManual->file_size / 1024, 2) }} KB</p>
                <p class="text-sm"><strong>Uploaded:</strong> {{ $currentManual->uploaded_at->format('F d, Y h:i A') }}</p>
                
                <div class="flex gap-3 mt-4">
                    <a href="{{ route('manual.view') }}" target="_blank" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-eye mr-2"></i>View Manual
                    </a>
                    
                    <form action="{{ route('manual.delete') }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete the current manual? Users will not be able to access it until a new one is uploaded.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash mr-2"></i>Delete Manual
                        </button>
                    </form>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-700">
                <i class="fas fa-info-circle mr-2"></i>
                No manual has been uploaded yet. Please upload a PDF file below.
            </p>
        @endif
    </div>

    {{-- Upload New Manual --}}
    <div class="p-4 border rounded-lg bg-gray-50">
        <h3 class="font-semibold text-lg mb-4">
            <i class="fas fa-upload text-blue-600 mr-2"></i>
            {{ $currentManual ? 'Replace Manual' : 'Upload Manual' }}
        </h3>
        
        @php
            $confirmMessage = $currentManual 
                ? 'This will replace the current manual. Continue?' 
                : 'Upload this manual?';
        @endphp
        
        <form action="{{ route('manual.upload') }}" method="POST" enctype="multipart/form-data" 
              onsubmit="return confirm('{{ $confirmMessage }}')">
            @csrf
            
            <div class="mb-4">
                <label for="manual_file" class="block text-sm font-medium text-gray-700 mb-2">
                    Select PDF File (Max 100MB)
                </label>
                <input type="file" 
                       name="manual_file" 
                       id="manual_file" 
                       accept=".pdf" 
                       required
                       class="block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-lg file:border-0
                              file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100
                              cursor-pointer">
                <p class="mt-1 text-xs text-gray-500">Only PDF files are allowed. Maximum file size: 100MB</p>
                
                @error('manual_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    <i class="fas fa-upload mr-2"></i>
                    {{ $currentManual ? 'Replace Manual' : 'Upload Manual' }}
                </button>
                
                <span id="file-name" class="text-sm text-gray-600 italic"></span>
            </div>
        </form>
    </div>
</div>

<script>
    // Show selected filename
    document.getElementById('manual_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || '';
        const fileSize = e.target.files[0]?.size || 0;
        const fileSizeMB = (fileSize / (1024 * 1024)).toFixed(2);
        
        if (fileName) {
            document.getElementById('file-name').textContent = `Selected: ${fileName} (${fileSizeMB} MB)`;
        } else {
            document.getElementById('file-name').textContent = '';
        }
    });
</script>
