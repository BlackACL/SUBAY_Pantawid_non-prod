<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🗂️ Inventory Management
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Session Messages --}}
            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded shadow-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded shadow-sm">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            {{-- Upload Section --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📤 Upload CSV</h3>
                <form id="csvUploadForm" action="{{ route('inventory.upload.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Choose a valid CSV file
                        </label>
                        <input type="file" name="csv_file" id="csv_file" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-300 focus:border-blue-500 text-sm text-gray-700">
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div id="progressBar" class="bg-blue-600 h-4 rounded-full" style="width: 0%"></div>
                        </div>
                        <span id="progressText" class="text-sm text-gray-700 mt-1 block">0%</span>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                            ⬆️ Upload CSV
                        </button>
                    </div>
                </form>
            </div>

            {{-- Export Section --}}
            <div class="bg-white shadow rounded-lg p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">📥 Export Inventory</h3>
                <p class="text-sm text-gray-600 mb-4">Click the button below to download the latest inventory data as a CSV file.</p>
                <div class="flex justify-end">
                    <a href="{{ route('inventory.export') }}"
                        class="inline-flex items-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-md shadow-sm transition">
                        ⬇️ Download CSV
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- AJAX & Progress Script --}}
    <script>
    document.getElementById('csvUploadForm').addEventListener('submit', function(e) {
        e.preventDefault(); // prevent normal form submission

        const form = e.target;
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressText.textContent = percent + '%';
            }
        });

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.message || 'Upload completed!');
                    progressBar.style.width = '0%';
                    progressText.textContent = '0%';
                    form.reset();
                } else {
                    alert('Upload failed. Please try again.');
                    progressBar.style.width = '0%';
                    progressText.textContent = '0%';
                }
            }
        };

        xhr.open('POST', form.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
    </script>
</x-app-layout>
