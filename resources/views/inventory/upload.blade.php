<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Inventory Management
        </h2>
    </x-slot>

    <div class="p-6">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Session Messages --}}
            <div id="sessionMessages">
                @if(session('success'))
                    <div id="successMessage" class="px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded shadow-sm mb-4 relative">
                        <button type="button" onclick="closeMessage('successMessage')" 
                            class="absolute top-2 right-2 text-green-700 hover:text-green-900 font-bold text-lg leading-none">
                            &times;
                        </button>
                        <div class="pr-8">✅ {{ session('success') }}</div>
                    </div>
                @endif
                @if(session('error'))
                    <div id="errorMessage" class="px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded shadow-sm mb-4 relative">
                        <button type="button" onclick="closeMessage('errorMessage')" 
                            class="absolute top-2 right-2 text-red-700 hover:text-red-900 font-bold text-lg leading-none">
                            &times;
                        </button>
                        <div class="pr-8">⚠️ {{ session('error') }}</div>
                    </div>
                @endif
            </div>

            {{-- Import Section --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📤 Upload Inventory Files</h3>
                <form id="csvUploadForm" action="{{ route('inventory.upload.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                            Choose one or more files (CSV, TXT, XLSX)
                        </label>
                        <input type="file" name="files[]" id="files" multiple required
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-300 focus:border-blue-500 text-sm text-gray-700">
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div id="uploadProgressBar" class="bg-blue-600 h-4 rounded-full" style="width: 0%"></div>
                        </div>
                        <span id="uploadProgressText" class="text-sm text-gray-700 mt-1 block">0%</span>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                            ⬆️ Upload
                        </button>
                        <button type="button" data-modal-target="clearInventoryModal"
                            class="inline-flex items-center px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                            🧹 Clear Inventory
                        </button>
                    </div>
                </form>
            </div>

            {{-- Import Report --}}
            <div id="importReport" class="space-y-4">
                @if(session('report'))
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">📄 Import Report</h3>
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">File</th>
                                    <th class="px-3 py-2">Inserted</th>
                                    <th class="px-3 py-2">Updated</th>
                                    <th class="px-3 py-2">Failed</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2">Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('report') as $index => $r)
                                    <tr class="border-b">
                                        <td class="px-3 py-2">{{ $r['file'] }}</td>
                                        <td class="px-3 py-2">
                                            @if(($r['processed'] ?? 0) > 0)
                                                <button type="button" onclick="toggleDataTable('inserted', '{{ $index }}')" 
                                                    class="text-blue-600 hover:text-blue-800 underline cursor-pointer">
                                                    {{ $r['processed'] ?? 0 }}
                                                </button>
                                            @else
                                                {{ $r['processed'] ?? 0 }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @if(($r['updated'] ?? 0) > 0)
                                                <button type="button" onclick="toggleDataTable('updated', '{{ $index }}')" 
                                                    class="text-green-600 hover:text-green-800 underline cursor-pointer">
                                                    {{ $r['updated'] ?? 0 }}
                                                </button>
                                            @else
                                                {{ $r['updated'] ?? 0 }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @if(($r['failed'] ?? 0) > 0)
                                                <button type="button" onclick="toggleDataTable('failed', '{{ $index }}')" 
                                                    class="text-red-600 hover:text-red-800 underline cursor-pointer">
                                                    {{ $r['failed'] ?? 0 }}
                                                </button>
                                            @else
                                                {{ $r['failed'] ?? 0 }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">{{ ucfirst($r['status']) }}</td>
                                        <td class="px-3 py-2 text-red-600">{{ $r['error'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Detailed Data Tables --}}
                        <div id="detailedDataTables" class="mt-6 space-y-4"></div>
                    </div>

                    {{-- Store report data for JavaScript --}}
                    <script type="application/json" id="report-data">
                        {!! json_encode(session('report') ?? []) !!}
                    </script>
                @endif
            </div>

            {{-- Export Section --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📥 Export Inventory</h3>
                <p class="text-sm text-gray-600 mb-4">Choose to export the full inventory or by specific file.</p>

                <form id="exportForm" action="{{ route('inventory.export') }}" method="GET" class="flex gap-2 items-center">
                    <select name="file" class="border border-gray-300 rounded-md p-2 text-sm">
                        <option value="">-- Full DB --</option>
                        @foreach($files ?? [] as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="inline-flex items-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                        ⬇️ Download
                    </button>
                </form>

                <div id="exportReport" class="mt-4"></div>
            </div>

        </div>
    </div>

    {{-- Clear Inventory Modal --}}
    <div id="clearInventoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">🧹 Clear Entire Inventory?</h3>
            <p class="text-sm text-gray-600 mb-4">
                This will remove <strong>all</strong> inventory data. Type <code>DELETE</code> and check the confirmation box to proceed.
            </p>
            <form id="clearInventoryForm" action="{{ route('inventory.clearinventory') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="text" name="confirm" placeholder="Type DELETE" class="border rounded p-2 w-full mb-2" required>
                <label class="inline-flex items-center gap-2 mb-4">
                    <input type="checkbox" required>
                    I confirm I want to clear all inventory
                </label>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('clearInventoryModal')"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Clear</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
    // Initialize report data
    const reportDataScript = document.getElementById('report-data');
    window.reportData = reportDataScript ? JSON.parse(reportDataScript.textContent) : [];
    
    // Close message function
    function closeMessage(messageId) {
        const message = document.getElementById(messageId);
        if (message) {
            message.style.display = 'none';
        }
    }
    
    // Toggle detailed data table
    function toggleDataTable(type, fileIndex) {
        const container = document.getElementById('detailedDataTables');
        const tableId = `${type}_table_${fileIndex}`;
        const existingTable = document.getElementById(tableId);
        
        // If table already exists, remove it
        if (existingTable) {
            existingTable.remove();
            return;
        }
        
        // Remove any other open tables
        container.innerHTML = '';
        
        const reportItem = window.reportData[fileIndex];
        if (!reportItem) return;
        
        let data = [];
        let title = '';
        let headerColor = '';
        
        switch(type) {
            case 'inserted':
                data = reportItem.inserted_data || [];
                title = `Inserted Records (${data.length})`;
                headerColor = 'bg-blue-100 text-blue-800';
                break;
            case 'updated':
                data = reportItem.updated_data || [];
                title = `Updated Records (${data.length})`;
                headerColor = 'bg-green-100 text-green-800';
                break;
            case 'failed':
                data = reportItem.failed_data || [];
                title = `Failed Records (${data.length})`;
                headerColor = 'bg-red-100 text-red-800';
                break;
        }
        
        if (data.length === 0) {
            container.innerHTML = `<div class="text-center py-4 text-gray-500">No ${type} records found.</div>`;
            return;
        }
        
        // Create table HTML
        let tableHTML = `
            <div id="${tableId}" class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold ${headerColor} px-3 py-1 rounded">${title}</h4>
                    <button onclick="document.getElementById('${tableId}').remove()" 
                        class="text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <table class="w-full text-sm border-collapse table-fixed">
                        <thead class="sticky top-0 bg-[#2e3192]">
                            <tr class="border-b">
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 8%;">No.</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 20%;">General Description</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 18%;">Serial No</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 15%;">Property No</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 12%;">PAR No</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 10%;">PAR Date</th>
                                <th class="px-2 py-2 font-medium text-white text-center" style="width: 7%;">Qty</th>
                                ${type === 'inserted' ? '<th class="px-2 py-2 font-medium text-white text-center" style="width: 10%;">Receiver</th>' : ''}
                                ${type === 'updated' ? '<th class="px-2 py-2 font-medium text-white text-center" style="width: 10%;">Receiver</th>' : ''}
                                ${type === 'failed' ? '<th class="px-2 py-2 font-medium text-white text-center" style="width: 15%;">Error</th>' : ''}
                            </tr>
                        </thead>
                        <tbody>`;
        
        data.forEach((row, index) => {
            tableHTML += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-2 py-2 text-center text-sm text-left">${index + 1}</td>
                    <td class="px-2 py-2 break-words text-sm text-left">${row.general_description}</td>
                    <td class="px-2 py-2 break-words text-sm text-left">${row.serial_no}</td>
                    <td class="px-2 py-2 break-words text-sm text-left">${row.property_no}</td>
                    <td class="px-2 py-2 break-words text-sm text-left">${row.par_no}</td>
                    <td class="px-2 py-2 text-center text-sm text-left">${row.par_date}</td>
                    <td class="px-2 py-2 text-center text-sm text-left">${row.qty}</td>
                    ${type === 'inserted' ? `<td class="px-2 py-2 break-words text-sm text-left">${row.receiver || 'N/A'}</td>` : ''}
                    ${type === 'updated' ? `<td class="px-2 py-2 break-words text-sm text-left">${row.receiver || 'N/A'}</td>` : ''}
                    ${type === 'failed' ? `<td class="px-2 py-2 text-red-600 break-words text-sm text-left">${row.error || 'Unknown error'}</td>` : ''}
                </tr>`;
        });
        
        tableHTML += `
                        </tbody>
                    </table>
                </div>
            </div>`;
        
        container.innerHTML = tableHTML;
    }

    // Modal toggle
    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-modal-target');
            const modal = document.getElementById(target);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // Upload with progress & report
    const uploadForm = document.getElementById('csvUploadForm');
    uploadForm.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();
        const progressBar = document.getElementById('uploadProgressBar');
        const progressText = document.getElementById('uploadProgressText');

        xhr.upload.addEventListener('progress', function(e){
            if(e.lengthComputable){
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressText.textContent = percent + '%';
            }
        });

        xhr.onreadystatechange = function(){
            if(xhr.readyState === XMLHttpRequest.DONE){
                progressBar.style.width = '0%';
                progressText.textContent = '0%';
                if(xhr.status === 200){
                    const response = JSON.parse(xhr.responseText);
                    const message = response.message || 'Upload completed!';
                    const report = response.report || [];
                    alert(message);

                    if(report.length > 0){
                        let html = `<div class="bg-white shadow rounded-lg p-6 mt-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">📄 Import Report</h3>
                            <table class="w-full text-sm text-left border-collapse">
                                <thead>
                                    <tr class="border-b">
                                        <th class="px-3 py-2">File</th>
                                        <th class="px-3 py-2">Inserted</th>
                                        <th class="px-3 py-2">Updated</th>
                                        <th class="px-3 py-2">Failed</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Error</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                        report.forEach((r, index) => {
                            html += `<tr class="border-b">
                                <td class="px-3 py-2">${r.file}</td>
                                <td class="px-3 py-2">
                                    ${(r.processed > 0) ? 
                                        `<button type="button" onclick="toggleDataTable('inserted', ${index})" 
                                            class="text-blue-600 hover:text-blue-800 underline cursor-pointer">
                                            ${r.processed}
                                        </button>` : 
                                        r.processed || 0}
                                </td>
                                <td class="px-3 py-2">
                                    ${(r.updated > 0) ? 
                                        `<button type="button" onclick="toggleDataTable('updated', ${index})" 
                                            class="text-green-600 hover:text-green-800 underline cursor-pointer">
                                            ${r.updated}
                                        </button>` : 
                                        r.updated || 0}
                                </td>
                                <td class="px-3 py-2">
                                    ${(r.failed > 0) ? 
                                        `<button type="button" onclick="toggleDataTable('failed', ${index})" 
                                            class="text-red-600 hover:text-red-800 underline cursor-pointer">
                                            ${r.failed}
                                        </button>` : 
                                        r.failed || 0}
                                </td>
                                <td class="px-3 py-2">${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</td>
                                <td class="px-3 py-2 text-red-600">${r.error || '-'}</td>
                            </tr>`;
                        });
                        html += `</tbody></table>
                                <div id="detailedDataTables" class="mt-6 space-y-4"></div>
                                </div>`;
                        document.getElementById('importReport').innerHTML = html;
                        
                        // Store report data for the detailed tables
                        window.reportData = report;
                    }
                    uploadForm.reset();
                } else {
                    alert('Upload failed. Check your files and try again.');
                }
            }
        };

        xhr.open('POST', uploadForm.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });

    // Export with dynamic report
    const exportForm = document.getElementById('exportForm');
    exportForm.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(exportForm);
        const params = new URLSearchParams(formData).toString();

        // Download CSV
        window.open(exportForm.action + '?' + params, '_blank');

        // AJAX report
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(xhr.readyState === XMLHttpRequest.DONE){
                if(xhr.status === 200){
                    const response = JSON.parse(xhr.responseText);
                    const report = response.report;

                    let html = `<div class="bg-white shadow rounded-lg p-6 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">📄 Export Report</h3>
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-3 py-2">File</th>
                                    <th class="px-3 py-2">Exported Rows</th>
                                    <th class="px-3 py-2">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="px-3 py-2">${report.file}</td>
                                    <td class="px-3 py-2">${report.exported_rows}</td>
                                    <td class="px-3 py-2">${report.timestamp}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>`;
                    document.getElementById('exportReport').innerHTML = html;
                } else {
                    alert('Export failed. Try again.');
                }
            }
        };
        xhr.open('GET', exportForm.action + '?' + params + '&ajax=1');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
    });
    </script>
</x-app-layout>
