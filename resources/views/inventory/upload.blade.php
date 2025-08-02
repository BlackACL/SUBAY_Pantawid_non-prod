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
                <form action="{{ route('inventory.upload.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Choose a valid CSV file
                        </label>
                        <input type="file" name="csv_file" id="csv_file" required
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-300 focus:border-blue-500 text-sm text-gray-700">
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
</x-app-layout>
