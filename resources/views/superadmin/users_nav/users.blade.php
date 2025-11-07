<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <style>
        .pulse {
            animation: pulse 1.5s ease-in-out infinite alternate;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.05); box-shadow: 0 0 20px rgba(34, 197, 94, 0.4); }
        }
        
        .relative {
            position: relative;
        }
    </style>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div id="success-alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            <button onclick="document.getElementById('success-alert').style.display='none'" class="absolute top-0 right-0 mt-2 mr-4 text-green-700 hover:text-green-900 text-2xl font-bold leading-none focus:outline-none">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div id="error-alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
            <button onclick="document.getElementById('error-alert').style.display='none'" class="absolute top-0 right-0 mt-2 mr-4 text-red-700 hover:text-red-900 text-2xl font-bold leading-none focus:outline-none">&times;</button>
        </div>
    @endif

    <div class="py-12" id="main-content">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="flex justify-between items-center mb-4">
                <form method="GET" action="{{ route('users') }}" id="filters-form" class="flex gap-2">
                    <!-- Province Dropdown -->
                    <div class="relative w-56">
                        <select name="province" id="filter-province" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Province</option>
                            <option value="DAVAO CITY" {{ request('province') == 'DAVAO CITY' ? 'selected' : '' }}>DAVAO CITY</option>
                            <option value="DAVAO DE ORO" {{ request('province') == 'DAVAO DE ORO' ? 'selected' : '' }}>DAVAO DE ORO</option>
                            <option value="DAVAO DEL NORTE" {{ request('province') == 'DAVAO DEL NORTE' ? 'selected' : '' }}>DAVAO DEL NORTE</option>
                            <option value="DAVAO DEL SUR" {{ request('province') == 'DAVAO DEL SUR' ? 'selected' : '' }}>DAVAO DEL SUR</option>
                            <option value="DAVAO OCCIDENTAL" {{ request('province') == 'DAVAO OCCIDENTAL' ? 'selected' : '' }}>DAVAO OCCIDENTAL</option>
                            <option value="DAVAO ORIENTAL" {{ request('province') == 'DAVAO ORIENTAL' ? 'selected' : '' }}>DAVAO ORIENTAL</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Municipality Dropdown -->
                    <div class="relative w-56">
                        <select name="municipality" id="filter-municipality" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Municipality</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <!-- Office Dropdown -->
                    <div class="relative w-56">
                        <select name="office" id="filter-office" class="border rounded px-3 py-2 pr-10 w-full appearance-none">
                            <option value="">Filter by Office</option>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>

                    @if(request('province') || request('municipality') || request('office'))
                        <a href="{{ route('users') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Clear Filters</a>
                    @endif
                </form>
            </div>

            {{-- Search + Buttons --}}
            <div class="flex justify-between items-center mb-6">
                <form method="GET" action="{{ route('users') }}" class="flex gap-2 w-full max-w-lg">
                    <div class="relative w-full">
                        <input type="text" name="search" id="searchInput" placeholder="Search by Company ID or Full Name..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Search</button>
                    @if(request('search'))
                        <a href="{{ route('users') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">Clear</a>
                    @endif
                </form>
                <div class="flex gap-2 ml-4">
                    <button onclick="openModal()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                    <button onclick="openImportModal()" 
                        class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-lg transition">
                        <i class="fas fa-file-upload"></i> Import Profiles
                    </button>
                </div>
            </div>

            {{-- Users Table --}}
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left rounded-lg overflow-hidden">
                        <thead class="text-xs font-semibold uppercase text-white bg-[#274C77]">
                            <tr>
                                <th class="px-4 py-3">No.</th>
                                <th class="px-6 py-3">Company ID</th>
                                <th class="px-6 py-3">Fullname</th>
                                <th class="px-6 py-3">Province</th>
                                <th class="px-6 py-3">Municipality</th>
                                <th class="px-6 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($users->count() === 0)
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-red-600 font-semibold text-lg">
                                        @if(request('province') || request('municipality') || request('office'))
                                            No users exist in
                                            @if(request('province')) <span>province <b>{{ request('province') }}</b></span>@endif
                                            @if(request('municipality')) <span>municipality <b>{{ request('municipality') }}</b></span>@endif
                                            @if(request('office')) <span>office <b>{{ request('office') }}</b></span>@endif.
                                        @else
                                            No users found.
                                        @endif
                                    </td>
                                </tr>
                            @else
                                @foreach ($users as $index => $user)
                                    <tr class="border-b {{ $loop->even ? 'bg-[#F8F9FA]' : 'bg-white' }} text-black shadow-sm text-md hover:bg-[#E3EAF3] transition" id="user-row-{{ $user->id }}">
                                        <td class="px-4 py-3">{{ $users->firstItem() + $index }}</td>
                                        <td class="px-6 py-3">{{ $user->company_id }}</td>
                                        <td class="px-6 py-3 text-left align-middle">
                                            <button
                                                onclick="openUserProfile('{{ $user->id }}')"
                                                class="user-fullname-btn inline-block text-left text-[#274C77] font-bold hover:underline cursor-pointer text-md"
                                            >
                                                {{ $user->fullname }}
                                            </button>
                                            @if($user->email)
                                                <span class="block text-xs text-gray-500 user-email mt-1">
                                                    {{ $user->email }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3">{{ $user->province }}</td>
                                        <td class="px-6 py-3">{{ $user->municipality }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="openEditModal('{{ $user->id }}')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-4 rounded">Edit</button>
                                                <button onclick="openArchiveModal('{{ $user->id }}', '{{ $user->fullname }}')" class="bg-red-700 hover:bg-red-800 text-white font-bold py-1 px-4 rounded">Archive</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Overlay (AddUser Form)-->
    <div id="modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <!-- Modal -->
        <div id="modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto relative">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">ADD USER</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form method="POST" action="{{ route('addusers.store') }}">
                        @csrf
                        <!-- Username -->
                        <div class="mb-4">
                            <label for="username" class="block font-medium">Username</label>
                            <input type="text" name="username" id="username" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block font-medium">Email</label>
                            <input type="email" name="email" id="email" class="w-full border rounded px-3 py-2" required value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Full Name (split into first/middle/last) -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="first_name" class="block font-medium">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="w-full border rounded px-3 py-2" required value="{{ old('first_name') }}">
                                @error('first_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="middle_name" class="block font-medium">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" class="w-full border rounded px-3 py-2" value="{{ old('middle_name') }}">
                                @error('middle_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="last_name" class="block font-medium">Last Name</label>
                                <input type="text" name="last_name" id="last_name" class="w-full border rounded px-3 py-2" required value="{{ old('last_name') }}">
                                @error('last_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <!-- Region | Province | Municipality -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="region" class="block font-medium">Region</label>
                                <select name="region" id="region" class="w-full border rounded px-3 py-2" required>
                                    <option value="Region XI">Region XI</option>
                                </select>
                            </div>
                            <div>
                                <label for="province" class="block font-medium">Province</label>
                                <select name="province" id="province" class="w-full border rounded px-3 py-2" required>
                                    <option value="">Select Province</option>
                                    <option value="DAVAO CITY">DAVAO CITY</option>
                                    <option value="DAVAO DE ORO">DAVAO DE ORO</option>
                                    <option value="DAVAO DEL NORTE">DAVAO DEL NORTE</option>
                                    <option value="DAVAO DEL SUR">DAVAO DEL SUR</option>
                                    <option value="DAVAO OCCIDENTAL">DAVAO OCCIDENTAL</option>
                                    <option value="DAVAO ORIENTAL">DAVAO ORIENTAL</option>
                                </select>
                            </div>
                            <div>
                                <label for="municipality" class="block font-medium">Municipality</label>
                                <select name="municipality" id="municipality" class="w-full border rounded px-3 py-2 w-64" required>
                                    <option value="">Select Municipality</option>
                                </select>
                            </div>
                        </div>
                        <!-- Office -->
                        <div class="mb-4">
                            <label for="office" class="block font-medium">Office</label>
                            <select name="office" id="office" class="w-full border rounded px-3 py-2 w-64" required>
                                <option value="">Select Office</option>
                            </select>
                        </div>
                        <!-- Employee Status | Company ID -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="employee_status" class="block font-medium">Employee Status</label>
                                <select name="employee_status" id="employee_status" class="w-full border rounded px-3 py-2" required>
                                    <option value="Regular" {{ old('employee_status') == 'Regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="Contractual" {{ old('employee_status') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                                    <option value="MOA" {{ old('employee_status') == 'MOA' ? 'selected' : '' }}>MOA</option>
                                </select>
                            </div>
                            <div>
                                <label for="company_id" class="block font-medium">Company ID</label>
                                <input type="text" name="company_id" id="company_id" class="w-full border rounded px-3 py-2" required value="{{ old('company_id') }}">
                                @error('company_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <!-- Access Level | Activated -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="access_level" class="block font-medium">Access Level</label>
                                <select name="access_level" id="access_level" class="w-full border rounded px-3 py-2">
                                    <option value="Superadmin">Superadmin</option>
                                    <option value="Regional DPSC">Regional DPSC</option>
                                    <option value="Provincial DPSC">Provincial DPSC</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>
                            <div>
                                <label for="activated" class="block font-medium">Activated</label>
                                <select name="activated" id="activated" class="w-full border rounded px-3 py-2">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <!-- Locked Status | Deleted Status -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="locked_status" class="block font-medium">Locked Status</label>
                                <select name="locked_status" id="locked_status" class="w-full border rounded px-3 py-2">
                                    <option value="No">Unlocked</option>
                                    <option value="Yes">Locked</option>
                                </select>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Profiles Modal -->
    <div id="import-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <div id="import-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
            <div id="modal-container" class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-full flex flex-col relative transition-all duration-300 ease-in-out p-7">
                <!-- Header -->
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-900">Import Employee Profiles</h3>
                    <button onclick="handleModalClose()" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <!-- Body -->
                <div class="mt-4">
                    <form id="import-form" method="POST" action="/emergency-test" enctype="multipart/form-data" onsubmit="return handleImportSubmit(event)">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv" required
                            class="w-full border rounded px-3 py-2 mb-4">

                        <!-- Progress Container -->
                        <div id="progress-container" class="w-full bg-gray-200 rounded-full h-6 relative hidden">
                            <!-- Progress Bar -->
                            <div id="progress-bar" 
                                class="bg-blue-600 h-6 rounded-full transition-all duration-500 ease-in-out" 
                                style="width: 0%">
                            </div>
                            <!-- Centered Text -->
                            <span id="progress-text" 
                                class="absolute inset-0 flex items-center justify-center font-bold text-black transition-opacity duration-700 opacity-100">
                                0%
                            </span>
                        </div>

                        <!-- Recent Log (inserted here) -->
                        <p id="recent-log" class="mt-2 text-sm text-gray-600"></p>

                        <!-- Transparency Log Container -->
                        <div id="transparency-log-container" class="hidden mt-4 border rounded-lg bg-gray-50">
                            <div class="p-3 border-b bg-gray-100 rounded-t-lg">
                                <h3 class="font-semibold text-gray-800 text-sm">Import Log</h3>
                                <p class="text-xs text-gray-600">Real-time processing details</p>
                            </div>
                            <div id="transparency-log" 
                                class="h-80 max-h-80 overflow-y-auto p-3 text-xs font-mono bg-white border-t space-y-1">
                                <div class="text-gray-500">Waiting for import to start...</div>
                            </div>
                        </div>

                        <!-- Updated Rows Container -->
                        <div id="updated-container" class="hidden mt-4 p-3 bg-blue-100 border border-blue-400 rounded">
                            <h3 class="font-semibold text-blue-800 mb-2">Updated Users:</h3>
                            <ul id="updated-list" class="list-disc list-inside text-sm text-blue-900"></ul>
                        </div>
                        
                        <!-- Skipped Rows Container -->
                        <div id="skipped-container" class="hidden mt-4 p-3 bg-yellow-100 border border-yellow-400 rounded">
                            <h3 class="font-semibold text-yellow-800 mb-2">Skipped Rows:</h3>
                            <ul id="skipped-list" class="list-disc list-inside text-sm text-yellow-900"></ul>
                        </div>
                        
                        <!-- Error Container -->
                        <div id="error-container" class="hidden mt-4 p-3 bg-red-100 border border-red-400 rounded text-red-800 text-sm">
                            <h3 class="font-semibold mb-2">Upload Error:</h3>
                            <p id="error-message"></p>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end">
                            <div class="flex gap-2">
                                <button type="submit" id="upload-btn"
                                    class="mt-4 bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-6 rounded-lg">
                                    Upload
                                </button>
                                <button type="button" id="done-btn" onclick="handleImportComplete()" 
                                    class="mt-4 bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded-lg pulse" 
                                    style="display: none;" disabled>
                                    DONE
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Modal Overlay -->
    <div id="user-profile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <!-- User Profile Modal -->
        <div id="user-profile-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto relative">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 bg-[#274c77] text-white rounded-t-lg">
                    <h3 class="text-lg font-semibold">User Profile</h3>
                    <button onclick="closeUserProfile()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div id="user-profile-content">
                        <!-- User Profile content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal Overlay -->
    <div id="archive-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <!-- Archive Confirmation Modal -->
        <div id="archive-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Archive</h3>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Archive User</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Are you sure you want to archive this user?
                        </p>
                        <p class="text-lg font-semibold text-gray-900 mb-6" id="archive-user-name">
                            <!-- User name will be inserted here -->
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50 rounded-b-lg">
                    <button onclick="closeArchiveModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                        No
                    </button>
                    <form id="archive-form" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Yes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto relative">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">EDIT USER</h3>
                    <button id="edit-close-btn" type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
                <div class="p-6">
                    <form id="edit-user-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit-user-id">

                        <!-- Username -->
                        <div class="mb-4">
                            <label class="block font-medium">Username</label>
                            <input type="text" id="edit-username" class="w-full border rounded px-3 py-2">
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block font-medium">Email</label>
                            <input type="email" id="edit-email" class="w-full border rounded px-3 py-2">
                        </div>

                        <!-- Full Name (split into first/middle/last) -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-medium">First Name</label>
                                <input type="text" id="edit-first_name" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block font-medium">Middle Name</label>
                                <input type="text" id="edit-middle_name" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block font-medium">Last Name</label>
                                <input type="text" id="edit-last_name" class="w-full border rounded px-3 py-2">
                            </div>
                        </div>

                        <!-- Region | Province | Municipality -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block font-medium">Region</label>
                                <select id="edit-region" class="w-full border rounded px-3 py-2">
                                    <option value="Region XI">Region XI</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium">Province</label>
                                <select id="edit-province" class="w-full border rounded px-3 py-2"></select>
                            </div>
                            <div>
                                <label class="block font-medium">Municipality</label>
                                <select id="edit-municipality" class="w-full border rounded px-3 py-2"></select>
                            </div>
                        </div>

                        <!-- Office -->
                        <div class="mb-4">
                            <label class="block font-medium">Office</label>
                            <select id="edit-office" class="w-full border rounded px-3 py-2"></select>
                        </div>

                        <!-- Employee Status | Company ID -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-medium">Employee Status</label>
                                <select id="edit-employee_status" class="w-full border rounded px-3 py-2">
                                    <option value="Regular">Regular</option>
                                    <option value="Contractual">Contractual</option>
                                    <option value="MOA">MOA</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium">Company ID</label>
                                <input type="text" id="edit-company_id" class="w-full border rounded px-3 py-2">
                            </div>
                        </div>

                        <!-- Access Level | Activated -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-medium">Access Level</label>
                                <select id="edit-access_level" class="w-full border rounded px-3 py-2">
                                    <option value="Superadmin">Superadmin</option>
                                    <option value="Regional DPSC">Regional DPSC</option>
                                    <option value="Provincial DPSC">Provincial DPSC</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium">Activated</label>
                                <select id="edit-activated" class="w-full border rounded px-3 py-2">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        <!-- Locked Status (single left column) -->
                        <div class="mb-4 w-full md:w-1/2">
                            <label class="block font-medium">Locked Status</label>
                            <select id="edit-locked_status" class="w-full border rounded px-3 py-2">
                                <option value="No">Unlocked</option>
                                <option value="Yes">Locked</option>
                            </select>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="discard-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="fixed inset-0 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full mx-4">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Discard changes?</h3>
                    <p class="text-gray-600 mb-6">You have unsaved changes. Are you sure you want to discard them?</p>
                    <div class="flex justify-center gap-4">
                        <button id="discard-yes-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Yes</button>
                        <button id="discard-no-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variable to track displayed transparency logs
        let displayedLogCount = 0;
        let isImportInProgress = false;
        let importCompleted = false;
        
        function escapeHtml(str) {
            if (str === undefined || str === null) return '';
            return String(str).replace(/[&<>"'`=\/]/g, function (s) {
                return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
                })[s];
            });
        }

        /* -------------------
        Helper: populate select options (small utility)
        ------------------- */
        function populateSelectOptions(selectElem, items, placeholderText = 'Select', selectedValue = '') {
            if (!selectElem) return;
            selectElem.innerHTML = '';
            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderText;
            selectElem.appendChild(placeholder);
            if (!Array.isArray(items) || items.length === 0) return;
            items.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                if (selectedValue !== undefined && selectedValue !== null && selectedValue.toString() === v.toString()) {
                    opt.selected = true;
                }
                selectElem.appendChild(opt);
            });
        }

        /* -------------------
        DATA MAPPINGS (shared for Add & Filters; Edit reuses these but via separate functions)
        (FULL lists provided below)
        ------------------- */
        // Dynamic data from database via Place model
        const provinceMunicipalityMap = @json($provinceMunicipalityMap);

        // Dynamic data from database via Place model
        const officeMap = @json($officeMap);

        /* ================================
        SHARED FUNCTIONS (for Add user modal AND the FILTERS on users page)
        - CHANGED: Add & Filters share these
        ================================ */
        function loadProvincesShared(selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            populateSelectOptions(sel, Object.keys(provinceMunicipalityMap), 'Select Province', selected ?? '');
        }
        function loadMunicipalitiesShared(province, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = provinceMunicipalityMap[province] || [];
            populateSelectOptions(sel, list, 'Select Municipality', selected ?? '');
        }
        function loadOfficesShared(municipality, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = officeMap[municipality] || [];
            populateSelectOptions(sel, list, 'Select Office', selected ?? '');
        }

        /* ================================
        EDIT-SPECIFIC FUNCTIONS (separate from Add/Filter)
        - CHANGED: ensure edit dropdowns are independent
        ================================ */
        function loadProvincesEdit(selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            populateSelectOptions(sel, Object.keys(provinceMunicipalityMap), 'Select Province', selected ?? '');
        }
        function loadMunicipalitiesEdit(province, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = provinceMunicipalityMap[province] || [];
            populateSelectOptions(sel, list, 'Select Municipality', selected ?? '');
        }
        function loadOfficesEdit(municipality, selectId, selected) {
            const sel = document.getElementById(selectId);
            if (!sel) return;
            const list = officeMap[municipality] || [];
            populateSelectOptions(sel, list, 'Select Office', selected ?? '');
        }

        /* -------------------
        Safe initial filter values (from Blade -> JS)
        CHANGED: use json_encode to avoid parse issues
        ------------------- */
        // Blade variables passed from server
        const initialFilterProvince     = '{{ request("province", "") }}';
        const initialFilterMunicipality = '{{ request("municipality", "") }}';
        const initialFilterOffice       = '{{ request("office", "") }}';

        /* -------------------
        DOMContentLoaded: wire Add modal, Filters, Edit modal listeners
        ------------------- */
        document.addEventListener('DOMContentLoaded', function() {
            /* ---------- FILTERS (users page) ----------
            IDs: filter-province, filter-municipality, filter-office
            These will use the SHARED functions (same behavior as Add modal)
            */
            const fProv = document.getElementById('filter-province');
            const fMun  = document.getElementById('filter-municipality');
            const fOff  = document.getElementById('filter-office');

            if (fProv) {
                loadProvincesShared('filter-province', initialFilterProvince || '');
                // if there's an initial province, load municipalities and offices
                if (initialFilterProvince) {
                    loadMunicipalitiesShared(initialFilterProvince, 'filter-municipality', initialFilterMunicipality || '');
                } else {
                    populateSelectOptions(fMun, [], 'Select Municipality');
                }
                if (initialFilterMunicipality) {
                    loadOfficesShared(initialFilterMunicipality, 'filter-office', initialFilterOffice || '');
                } else {
                    populateSelectOptions(fOff, [], 'Select Office');
                }

                fProv.addEventListener('change', function(e) {
                    const prov = e.target.value;
                    loadMunicipalitiesShared(prov, 'filter-municipality');
                    populateSelectOptions(fOff, [], 'Select Office'); // clear offices when province changes
                });
                fMun && fMun.addEventListener('change', function(e) {
                    loadOfficesShared(e.target.value, 'filter-office');
                });
            }

            /* ---------- ADD USER modal ----------
            IDs: province, municipality, office
            Uses the same SHARED functions as Filters above
            */
            const addProv = document.getElementById('province');
            const addMun  = document.getElementById('municipality');
            const addOff  = document.getElementById('office');

            if (addProv) {
                loadProvincesShared('province'); // no preselect for add modal
                addProv.addEventListener('change', function(e) {
                    loadMunicipalitiesShared(e.target.value, 'municipality');
                    populateSelectOptions(addOff, [], 'Select Office');
                });
                addMun && addMun.addEventListener('change', function(e) {
                    loadOfficesShared(e.target.value, 'office');
                });
            }

            /* ---------- EDIT modal ----------
            IDs: edit-province, edit-municipality, edit-office
            These are *separate* and use edit-specific functions
            */
            const editProv = document.getElementById('edit-province');
            const editMun  = document.getElementById('edit-municipality');
            const editOff  = document.getElementById('edit-office');

            if (editProv) {
                // populate provinces blank initially; values set when openEditModal() runs
                loadProvincesEdit('edit-province');
                editProv.addEventListener('change', function(e) {
                    loadMunicipalitiesEdit(e.target.value, 'edit-municipality');
                    populateSelectOptions(editOff, [], 'Select Office');
                });
                editMun && editMun.addEventListener('change', function(e) {
                    loadOfficesEdit(e.target.value, 'edit-office');
                });
            }
        });

        // Track changes in edit modal
        let editFormInitialData = {};
        function storeEditFormInitialData() {
            const form = document.getElementById('edit-user-form');
            if (!form) return;
            editFormInitialData = {};
            Array.from(form.elements).forEach(el => {
                if (el.id) editFormInitialData[el.id] = el.value;
            });
        }
        function hasEditFormChanged() {
            const form = document.getElementById('edit-user-form');
            if (!form) return false;
            return Array.from(form.elements).some(el => {
                if (!el.id) return false;
                return editFormInitialData[el.id] !== el.value;
            });
        }

        // When opening edit modal, store initial data
        function openEditModalUI() {
            document.getElementById('edit-modal-overlay')?.classList.remove('hidden');
            document.getElementById('edit-modal')?.classList.remove('hidden');
            document.getElementById('main-content')?.classList.add('blur-md');
        }

        // Override closeEditModal to check for changes
        function closeEditModal() {
            if (hasEditFormChanged()) {
                document.getElementById('discard-modal-overlay')?.classList.remove('hidden');
            } else {
                actuallyCloseEditModal();
            }
        }

        // Function to close modal after successful update (bypasses change detection)
        function closeEditModalAfterUpdate() {
            actuallyCloseEditModal();
        }

        function actuallyCloseEditModal() {
            document.getElementById('edit-modal-overlay')?.classList.add('hidden');
            document.getElementById('edit-modal')?.classList.add('hidden');
            document.getElementById('main-content')?.classList.remove('blur-md');
            document.getElementById('discard-modal-overlay')?.classList.add('hidden');
        }

        // Wire discard modal buttons
        document.getElementById('discard-yes-btn')?.addEventListener('click', function() {
            actuallyCloseEditModal();
        });
        document.getElementById('discard-no-btn')?.addEventListener('click', function() {
            document.getElementById('discard-modal-overlay')?.classList.add('hidden');
        });

        /* -------------------
        Modal open/close helpers for Add modal
        ------------------- */
        function openModal() {
            document.getElementById('modal-overlay')?.classList.remove('hidden');
            document.getElementById('modal')?.classList.remove('hidden');
            document.getElementById('main-content')?.classList.add('blur-md');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('modal-overlay')?.classList.add('hidden');
            document.getElementById('modal')?.classList.add('hidden');
            document.getElementById('main-content')?.classList.remove('blur-md');
            document.body.style.overflow = 'auto';
        }
        document.getElementById('modal-overlay')?.addEventListener('click', function(e) { if (e.target === this) closeModal(); });

        /* -------------------
        User profile modal helpers
        ------------------- */
        function openUserProfile(userId) {
            const container = document.getElementById('user-profile-content');
            if (!container) return;
            container.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading profile...</p></div>';
            document.getElementById('user-profile-overlay')?.classList.remove('hidden');
            document.getElementById('user-profile-modal')?.classList.remove('hidden');
            document.getElementById('main-content')?.classList.add('blur-md');

            fetch(`/users/${userId}/profile`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        container.innerHTML = generateUserProfileHTML(data.user);
                    } else {
                        container.innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                });
        }
        function closeUserProfile() {
            document.getElementById('user-profile-overlay')?.classList.add('hidden');
            document.getElementById('user-profile-modal')?.classList.add('hidden');
            document.getElementById('main-content')?.classList.remove('blur-md');
        }
        function generateUserProfileHTML(user) {
            return `
                <div>
                    <div class="flex items-start space-x-6 mb-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">${user.fullname ?? ''}</h2>
                            <p class="text-gray-600 mb-1">${user.company_id ?? ''}</p>
                            <p class="text-black font-medium mb-4">${user.access_level ?? ''}</p>
                        </div>
                    </div>
                    <hr class="my-4 border-black border-t-2">
                    <div class="flex w-full">
                        <div class="space-y-3 flex-1">
                            <div><span class="font-bold text-black">Email:</span> <span class="ml-2 text-gray-900">${user.email ?? ''}</span></div>
                            <div><span class="font-bold text-black">Username:</span> <span class="ml-2 text-gray-900">${user.username ?? ''}</span></div>
                            <div><span class="font-bold text-black">Employee Status:</span> <span class="ml-2 text-gray-900">${user.employee_status ?? ''}</span></div>
                            <div><span class="font-bold text-black">Locked Status:</span> <span class="ml-2 text-gray-900">${user.locked_status ?? ''}</span></div>
                            <div><span class="font-bold text-black">Activated:</span> <span class="ml-2 text-gray-900">${user.activated ?? ''}</span></div>
                        </div>
                        <div class="space-y-3 flex-1">
                            <div><span class="font-bold text-black">Region:</span> <span class="ml-2 text-gray-900">${user.region ?? ''}</span></div>
                            <div><span class="font-bold text-black">Province:</span> <span class="ml-2 text-gray-900">${user.province ?? ''}</span></div>
                            <div><span class="font-bold text-black">Municipality:</span> <span class="ml-2 text-gray-900">${user.municipality ?? ''}</span></div>
                            <div><span class="font-bold text-black">Office:</span> <span class="ml-2 text-gray-900">${user.office ?? ''}</span></div>
                        </div>
                    </div>
                </div>
            `;
        }

        /* -------------------
        Archive modal helpers
        ------------------- */
        function openArchiveModal(userId, userName) {
            const nameEl = document.getElementById('archive-user-name');
            if (nameEl) nameEl.textContent = userName ?? '';
            const frm = document.getElementById('archive-form');
            if (frm) frm.action = `/users/${userId}/archive`;
            document.getElementById('archive-modal-overlay')?.classList.remove('hidden');
            document.getElementById('archive-modal')?.classList.remove('hidden');
            document.getElementById('main-content')?.classList.add('blur-md');
        }
        function closeArchiveModal() {
            document.getElementById('archive-modal-overlay')?.classList.add('hidden');
            document.getElementById('archive-modal')?.classList.add('hidden');
            document.getElementById('main-content')?.classList.remove('blur-md');
        }
        document.getElementById('archive-modal-overlay')?.addEventListener('click', function(e){ if (e.target === this) closeArchiveModal(); });

        /* -------------------
        EDIT modal: open, prefill & wire (CHANGED)
        - Prefill sequence ensures province loads first, then municipality, then office
        ------------------- */
        // base URL to build /users/{id} endpoint (Blade will render the correct base)
            const usersBaseUrl = '{{ url("users") }}';

            // Helper: try to split fullname into parts.
            // Expected storage format (your compose): "LASTNAME , FIRSTNAME MIDDLENAME"
            // But function handles simple "Firstname Middlename Lastname" and other common patterns defensively.
            function splitFullname(fullname) {
                fullname = (fullname || '').trim();
                if (!fullname) return { first_name: '', middle_name: '', last_name: '' };

                // If the fullname contains a comma -> assume "LASTNAME , FIRST MID"
                if (fullname.includes(',')) {
                    const [lastPart, rest] = fullname.split(',').map(s => s.trim());
                    const restParts = (rest || '').split(/\s+/).filter(Boolean);
                    const first = restParts[0] || '';
                    const middle = restParts.slice(1).join(' ') || '';
                    return { first_name: first, middle_name: middle, last_name: lastPart };
                }

                // Otherwise try "First Middle Last" splitting heuristically
                const parts = fullname.split(/\s+/).filter(Boolean);
                if (parts.length === 1) {
                    return { first_name: parts[0], middle_name: '', last_name: '' };
                } else if (parts.length === 2) {
                    return { first_name: parts[0], middle_name: '', last_name: parts[1] };
                } else {
                    const first = parts[0];
                    const last = parts[parts.length - 1];
                    const middle = parts.slice(1, -1).join(' ');
                    return { first_name: first, middle_name: middle, last_name: last };
                }
            }

            // attach edit-close button & overlay wiring in DOMContentLoaded
            document.addEventListener('DOMContentLoaded', function() {
                // ... leave your existing wiring in place for filters/add modals etc ...

                // Wire edit close button (HTML now has id="edit-close-btn")
                const editCloseBtn = document.getElementById('edit-close-btn');
                if (editCloseBtn) editCloseBtn.addEventListener('click', closeEditModal);

                // allow clicking overlay to close edit modal
                const editOverlay = document.getElementById('edit-modal-overlay');
                if (editOverlay) {
                    editOverlay.addEventListener('click', function(e) {
                        if (e.target === this) closeEditModal();
                    });
                }
            });

            function openEditModalUI() {
                document.getElementById('edit-modal-overlay')?.classList.remove('hidden');
                document.getElementById('edit-modal')?.classList.remove('hidden');
                document.getElementById('main-content')?.classList.add('blur-md');
            }

            /* Open Edit modal: fetch profile and prefill (split name fields)
            NOTE: This version first checks if the returned user object already has
            first_name/middle_name/last_name. If not, it falls back to parseFullname(fullname).
            */
            async function openEditModal(userId) {
                try {
                    const res = await fetch(`/users/${userId}/profile`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (!data.success) {
                        alert('Failed to load user profile');
                        return;
                    }
                    const u = data.user || {};

                    // fill simple inputs
                    document.getElementById('edit-user-id').value = u.id ?? '';
                    document.getElementById('edit-username').value = u.username ?? '';
                    document.getElementById('edit-email').value = u.email ?? '';

                    // Prefill split name fields:
                    // prefer explicit fields from the API if present, otherwise parse fullname
                    if (u.first_name !== undefined || u.last_name !== undefined || u.middle_name !== undefined) {
                        document.getElementById('edit-first_name').value = u.first_name ?? '';
                        document.getElementById('edit-middle_name').value = u.middle_name ?? '';
                        document.getElementById('edit-last_name').value = u.last_name ?? '';
                    } else {
                        const parts = parseFullname(u.fullname ?? '');
                        document.getElementById('edit-first_name').value = parts.first ?? '';
                        document.getElementById('edit-middle_name').value = parts.middle ?? '';
                        document.getElementById('edit-last_name').value = parts.last ?? '';
                    }

                    document.getElementById('edit-region').value = u.region ?? 'Region XI';
                    document.getElementById('edit-company_id').value = u.company_id ?? '';
                    document.getElementById('edit-employee_status').value = u.employee_status ?? '';
                    document.getElementById('edit-access_level').value = u.access_level ?? '';
                    document.getElementById('edit-activated').value = u.activated ?? '';
                    document.getElementById('edit-locked_status').value = u.locked_status ?? '';

                    // load selects in sequence and set selected values
                    loadProvincesEdit('edit-province', u.province ?? '');
                    setTimeout(() => {
                        loadMunicipalitiesEdit(u.province ?? '', 'edit-municipality', u.municipality ?? '');
                        setTimeout(() => {
                            loadOfficesEdit(u.municipality ?? '', 'edit-office', u.office ?? '');
                            // Store initial data AFTER all form fields are populated
                            setTimeout(() => {
                                storeEditFormInitialData();
                            }, 50);
                        }, 30);
                    }, 30);

                    openEditModalUI();
                } catch (err) {
                    console.error('Error opening edit modal:', err);
                    alert('Error fetching profile');
                }
            }

            /* Edit form submit handler (POST + _method: 'PUT' method spoofing)
            Improved error handling to surface validation errors returned by Laravel
            */
            document.getElementById('edit-user-form')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                const id = document.getElementById('edit-user-id')?.value;
                if (!id) { alert('Missing user id'); return; }

                const payload = {
                    username: document.getElementById('edit-username')?.value ?? '',
                    email: document.getElementById('edit-email')?.value ?? '',
                    first_name: document.getElementById('edit-first_name')?.value ?? '',
                    middle_name: document.getElementById('edit-middle_name')?.value ?? '',
                    last_name: document.getElementById('edit-last_name')?.value ?? '',
                    region: document.getElementById('edit-region')?.value ?? '',
                    province: document.getElementById('edit-province')?.value ?? '',
                    municipality: document.getElementById('edit-municipality')?.value ?? '',
                    office: document.getElementById('edit-office')?.value ?? '',
                    employee_status: document.getElementById('edit-employee_status')?.value ?? '',
                    company_id: document.getElementById('edit-company_id')?.value ?? '',
                    access_level: document.getElementById('edit-access_level')?.value ?? '',
                    activated: document.getElementById('edit-activated')?.value ?? '',
                    locked_status: document.getElementById('edit-locked_status')?.value ?? ''
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                || document.querySelector('input[name="_token"]')?.value
                                || '';

                const url = `${usersBaseUrl}/${encodeURIComponent(id)}`;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(Object.assign({ _method: 'PUT', _token: csrfToken }, payload))
                    });

                    const text = await res.text();
                    let data = null;
                    try { data = text ? JSON.parse(text) : {}; } catch (err) { data = null; }

                    if (!res.ok) {
                        // If Laravel returns validation errors: data.errors is an object of arrays
                        if (data && data.errors) {
                            // flatten and show first error (you can show more if desired)
                            const first = Object.values(data.errors).flat()[0] || 'Validation failed';
                            alert('Update failed: ' + first);
                        } else if (data && data.message) {
                            alert('Update failed: ' + data.message);
                        } else {
                            alert('Update failed: HTTP ' + res.status);
                        }
                        console.error('Update failed', res.status, text, data);
                        return;
                    }

                    if (data && data.success) {
                        const row = document.getElementById(`user-row-${id}`);
                        if (row && data.user) {
                            // company id (cell index 1)
                            row.children[1].textContent = data.user.company_id ?? row.children[1].textContent;

                            // fullname cell (index 2) — update button text only
                            const fullnameCell = row.children[2];
                            const fullnameBtn = fullnameCell.querySelector('.user-fullname-btn');
                            if (fullnameBtn) {
                                fullnameBtn.textContent = data.user.fullname ?? fullnameBtn.textContent;
                                // keep onclick; optionally update it (id is unchanged usually)
                                fullnameBtn.setAttribute('onclick', `openUserProfile(${encodeURIComponent(id)})`);
                            } else {
                                // fallback: recreate button and email without breaking layout
                                const fullnameHtml = `<button onclick="openUserProfile(${encodeURIComponent(id)})" class="user-fullname-btn text-[#274C77] font-bold hover:underline cursor-pointer text-md">${escapeHtml(data.user.fullname ?? '')}</button>`;
                                const emailHtml = data.user.email ? `<br><span class="text-xs text-gray-500 user-email">${escapeHtml(data.user.email)}</span>` : '';
                                fullnameCell.innerHTML = fullnameHtml + emailHtml;
                            }

                            // update email if present in same cell (optional)
                            const emailSpan = fullnameCell.querySelector('.user-email');
                            if (emailSpan) {
                                emailSpan.textContent = data.user.email ?? '';
                            }

                            // province/municipality (no office column in this table)
                            row.children[3].textContent = data.user.province ?? row.children[3].textContent;
                            row.children[4].textContent = data.user.municipality ?? row.children[4].textContent;
                        }
                        closeEditModalAfterUpdate();
                        return;
                    }

                    alert('Update failed: Unexpected server response. Check console.');
                    console.warn('Unexpected response', data, text);
                } catch (err) {
                    console.error('Error updating user:', err);
                    alert('Error updating user. See console & laravel logs for details.');
                }
            });
        /* -------------------
        Import Profiles modal + upload + polling (FIXED VERSION)
        ------------------- */
        function openImportModal() {
            document.getElementById('import-modal-overlay').classList.remove('hidden');
            document.getElementById('import-modal').classList.remove('hidden');
            document.getElementById('main-content').classList.add('blur-md');
            document.body.style.overflow = 'hidden'; 
        }

        function closeImportModal() {
            document.getElementById('import-modal-overlay').classList.add('hidden');
            document.getElementById('import-modal').classList.add('hidden');
            document.getElementById('main-content').classList.remove('blur-md');
            document.body.style.overflow = 'auto';
        }

        // CORRECTED: Proper startPolling function
        async function startPolling() {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            if (!progressBar || !progressText) {
                console.error('Progress elements not found');
                return;
            }

            const interval = setInterval(async () => {
                try {
                    const res = await fetch('/import/progress');
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await res.json();
                    console.log('Polling data:', data); // Debug log

                    if (data && data.total > 0) {
                        const percent = Math.round((data.processed / data.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressText.innerText = percent + '%';

                        // Update transparency log if new logs available
                        if (data.logs && data.logs.length > 0) {
                            updateTransparencyLog(data.logs);
                        }

                        if (percent >= 100) {
                            clearInterval(interval);
                            progressText.innerText = '100% ✓ Import Complete!';
                            progressBar.classList.remove('bg-blue-500');
                            progressBar.classList.add('bg-green-500');
                            
                            // Enable DONE button and update import state
                            const doneBtn = document.getElementById('done-btn');
                            if (doneBtn) {
                                doneBtn.disabled = false;
                                doneBtn.textContent = 'DONE';
                                doneBtn.classList.add('btn-pulse'); // Add pulsing effect
                            }
                            isImportInProgress = false;
                            importCompleted = true;
                            
                            // Don't auto-reload, wait for user to click DONE
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                    progressText.innerText = 'Error polling progress';
                }
            }, 1000); // Poll every second
        }

        // CORRECTED: uploadCSV function
        function uploadCSV(event) {
            event.preventDefault();

            const form = document.getElementById('import-form');
            if (!form) {
                console.error('Import form not found');
                return;
            }

            console.log('Import form action:', form.action);
            const formData = new FormData(form);
            const progressBar = document.getElementById('progress-bar');
            const progressContainer = document.getElementById('progress-container');
            const progressText = document.getElementById('progress-text');

            if (!progressBar || !progressContainer || !progressText) {
                console.error('Progress elements not found');
                return;
            }

            // Show progress container
            progressContainer.classList.remove('hidden');
            progressBar.style.width = "0%";
            progressText.innerText = "0%";

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || document.querySelector('input[name="_token"]')?.value;

            if (!csrfToken) {
                console.error('CSRF token not found');
                alert('Security token missing. Please refresh the page.');
                return;
            }

            // Start polling for progress handled by handleImportSubmit instead
            // startPolling(); // DISABLED - using pollProgress() instead

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                console.log('Parsed response:', data);

                // Display updated users if any
                if (data.updated && data.updated.length > 0) {
                    const updatedContainer = document.getElementById('updated-container');
                    const updatedList = document.getElementById('updated-list');
                    updatedList.innerHTML = '';
                    data.updated.forEach(message => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        updatedList.appendChild(li);
                    });
                    updatedContainer.classList.remove('hidden');
                }
                
                // Display skipped users if any
                if (data.skipped && data.skipped.length > 0) {
                    const skippedContainer = document.getElementById('skipped-container');
                    const skippedList = document.getElementById('skipped-list');
                    skippedList.innerHTML = '';
                    data.skipped.forEach(message => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        skippedList.appendChild(li);
                    });
                    skippedContainer.classList.remove('hidden');
                }
                // The page will reload from the polling function when progress reaches 100%
            })
            .catch(err => {
                progressBar.classList.remove("bg-blue-600");
                progressBar.classList.add("bg-red-600");
                progressText.innerText = "Upload Failed - Server Error";
                console.error('Upload failed:', err);
            });
        }

        function showError(message) {
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            
            if (errorContainer && errorMessage) {
                errorMessage.textContent = message;
                errorContainer.classList.remove('hidden');
            }
        }

        // Handle import form submission with progress tracking
        function handleImportSubmit(event) {
            event.preventDefault();
            
            const form = document.getElementById('import-form');
            const formData = new FormData(form);
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const recentLog = document.getElementById('recent-log');
            const uploadBtn = document.getElementById('upload-btn');
            const doneBtn = document.getElementById('done-btn');
            
            console.log('Starting CSV import...');
            
            // Set import state
            isImportInProgress = true;
            importCompleted = false;
            
            // Get the selected file name and modify the file input display
            const fileInput = form.querySelector('input[type="file"]');
            const fileName = fileInput.files[0] ? fileInput.files[0].name : 'Unknown file';
            
            // Replace file input with upload message
            const fileInputContainer = fileInput.parentElement;
            const uploadMessage = document.createElement('div');
            uploadMessage.className = 'w-full border rounded px-3 py-2 mb-4 bg-blue-50 border-blue-300 text-blue-800 font-semibold flex items-center';
            uploadMessage.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                File&nbsp;<em>${fileName}</em>&nbsp;is Uploading!
            `;
            fileInput.style.display = 'none';
            fileInputContainer.insertBefore(uploadMessage, fileInput);
            
            // Switch buttons
            uploadBtn.style.display = 'none';
            doneBtn.style.display = 'inline-block';
            doneBtn.disabled = true;
            doneBtn.textContent = 'UPLOADING...';
            
            // Show progress bar and reset state
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressBar.classList.remove("bg-red-600");
            progressBar.classList.add("bg-blue-600");
            progressText.textContent = '0% - Preparing to import...';
            if (recentLog) recentLog.textContent = 'Starting import process...';
            
            // Show and initialize transparency log
            const transparencyLogContainer = document.getElementById('transparency-log-container');
            const transparencyLog = document.getElementById('transparency-log');
            if (transparencyLogContainer) transparencyLogContainer.classList.remove('hidden');
            if (transparencyLog) {
                transparencyLog.innerHTML = '<div class="text-blue-600 font-semibold"><i class="fas fa-play-circle mr-2"></i>Import starting...</div>';
            }
            
            // IMMEDIATELY expand modal to full size when upload starts
            const modalContainer = document.getElementById('modal-container');
            if (modalContainer) {
                modalContainer.classList.remove('max-w-lg');
                modalContainer.classList.add('max-w-6xl'); // Wider modal
                modalContainer.style.height = '90vh'; // Taller height to fit everything
                modalContainer.style.maxHeight = '90vh';
                modalContainer.style.minHeight = '90vh';
                modalContainer.style.padding = '24px'; // Ensure adequate padding
                console.log('Modal expanded to FULL SIZE with FIXED HEIGHT immediately');
            }
            
            // Reset transparency log counter
            displayedLogCount = 0;
            
            // Clear any old progress data first
            fetch('/import/clear-progress', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(() => {
                console.log('Old progress cleared, starting import...');
                
                // Start polling early to catch initial progress
                setTimeout(pollProgress, 200);
                
                // Submit form
                return fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            })
            .then(response => {
                console.log('Upload response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Upload completed:', data);
                if (data.success) {
                    // Don't override progress text - let polling handle it
                    console.log('Import started successfully, continuing to poll for progress');
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                progressBar.classList.remove("bg-blue-600");
                progressBar.classList.add("bg-red-600");
                progressText.textContent = 'Import failed';
                if (recentLog) recentLog.textContent = 'Error: ' + error.message;
                showError('Upload failed: ' + error.message);
            });
            
            return false;
        }

        // Update transparency log with new entries
        function updateTransparencyLog(allLogs) {
            const transparencyLog = document.getElementById('transparency-log');
            if (!transparencyLog || !Array.isArray(allLogs)) return;
            
            // Clear existing logs and rebuild from scratch to ensure we show everything
            transparencyLog.innerHTML = '<div class="text-blue-600 font-semibold mb-2"><i class="fas fa-upload mr-2"></i>Import Progress</div>';
            
            allLogs.forEach(logEntry => {
                const logDiv = document.createElement('div');
                logDiv.className = 'mb-1 py-1 border-b border-gray-100 text-sm';
                
                // Color code based on status
                let statusColor = 'text-gray-700';
                let statusIcon = '<i class="fas fa-info-circle"></i>';
                
                if (logEntry.status === 'created') {
                    statusColor = 'text-green-700';
                    statusIcon = '<i class="fas fa-check-circle"></i>';
                } else if (logEntry.status === 'skipped') {
                    statusColor = 'text-yellow-700';
                    statusIcon = '<i class="fas fa-exclamation-triangle"></i>';
                } else if (logEntry.status === 'error') {
                    statusColor = 'text-red-700';
                    statusIcon = '<i class="fas fa-times-circle"></i>';
                } else if (logEntry.status === 'duplicate') {
                    statusColor = 'text-blue-700';
                    statusIcon = '<i class="fas fa-copy"></i>';
                } else if (logEntry.status === 'completed') {
                    statusColor = 'text-purple-700';
                    statusIcon = '<i class="fas fa-trophy"></i>';
                }
                
                // Use timestamp from log or current time
                const timestamp = logEntry.timestamp ? 
                    new Date(logEntry.timestamp).toLocaleTimeString() : 
                    new Date().toLocaleTimeString();
                
                logDiv.innerHTML = `
                    <span class="text-gray-500 text-xs block">${timestamp}</span>
                    <span class="${statusColor} font-medium">
                        ${statusIcon} ${logEntry.message}
                    </span>
                `;
                
                transparencyLog.appendChild(logDiv);
            });
            
            // Auto-scroll to bottom to show latest entries
            transparencyLog.scrollTop = transparencyLog.scrollHeight;
        }

        // Poll for progress updates
        function pollProgress() {
            fetch('/import/progress', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Progress data received:', data); // Debug log
                
                    if (data && typeof data.total === 'number' && data.total > 0) {
                        const progressBar = document.getElementById('progress-bar');
                        const progressText = document.getElementById('progress-text');
                        const recentLog = document.getElementById('recent-log');
                        
                        // Calculate percentage from processed/total
                        const percent = Math.round((data.processed / data.total) * 100);
                        console.log(`Updating progress: ${percent}% (${data.processed}/${data.total})`);
                        
                        if (progressBar) {
                            progressBar.style.width = percent + '%';
                        }
                        
                        if (progressText) {
                            progressText.textContent = `${percent}%`;
                        }
                        
                        if (recentLog && data.recent) {
                            recentLog.textContent = data.recent;
                        }
                        
                        // Update transparency log with detailed entries
                        if (data.logs && data.logs.length > 0) {
                            updateTransparencyLog(data.logs);
                        }
                    
                    if (percent < 100) {
                        // Continue polling more frequently during active import
                        console.log('Continuing to poll for progress...');
                        setTimeout(pollProgress, 300); // Poll every 0.3 seconds for faster updates
                    } else {
                        // Import complete - enable DONE button
                        console.log('Import completed! Setting to 100% complete');
                        
                        // Change progress bar to green when complete
                        if (progressBar) {
                            progressBar.classList.remove("bg-blue-600");
                            progressBar.classList.add("bg-green-600");
                        }
                        
                        if (progressText) {
                            console.log('Setting progress text to 100% complete');
                            progressText.textContent = '100% ✓ Import Complete!';
                        }
                        if (recentLog) {
                            recentLog.textContent = `Import finished! Created: ${data.processed} users`;
                        }
                        
                        // Enable DONE button and update import state
                        const doneBtn = document.getElementById('done-btn');
                        console.log('Found DONE button:', doneBtn); // Debug
                        if (doneBtn) {
                            doneBtn.style.display = 'inline-block';
                            doneBtn.disabled = false;
                            doneBtn.textContent = 'DONE';
                            doneBtn.classList.add('pulse'); // Add pulsing effect
                            console.log('DONE button enabled and shown'); // Debug
                        } else {
                            console.error('DONE button not found!');
                        }
                        
                        // Update import state
                        isImportInProgress = false;
                        importCompleted = true;
                        
                        // Update the upload message to show completion
                        const form = document.getElementById('import-form');
                        if (form) {
                            const uploadMessage = form.querySelector('div.bg-blue-50');
                            if (uploadMessage) {
                                uploadMessage.className = 'w-full border rounded px-3 py-2 mb-4 bg-green-50 border-green-300 text-green-800 font-semibold flex items-center';
                                uploadMessage.innerHTML = `
                                    <svg class="h-4 w-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    File&nbsp;<em>${uploadMessage.textContent.match(/File (.+?) is/)?.[1] || 'upload'}</em>&nbsp;uploaded successfully!
                                `;
                            }
                        }
                        
                        // Ensure completion message appears - add it manually if not in backend logs
                        const existingLogs = data.logs || [];
                        const hasCompletionMessage = existingLogs.some(log => log.status === 'completed');
                        
                        if (!hasCompletionMessage) {
                            // Add completion message manually
                            const updatedCount = data.updated ? data.updated.length : 0;
                            const skippedCount = data.skipped ? data.skipped.length : 0;
                            const createdCount = data.processed - updatedCount;
                            
                            const completionMessage = {
                                status: 'completed',
                                message: `Import completed! Created: ${createdCount}, Updated: ${updatedCount}, Skipped: ${skippedCount}`,
                                timestamp: new Date().toISOString()
                            };
                            existingLogs.push(completionMessage);
                            updateTransparencyLog(existingLogs);
                        }
                        
                        console.log('Import completion detected, completion message should be visible');
                        
                        // NO AUTO-RELOAD - wait for user to click DONE
                    }
                } else {
                    // No valid data yet, keep polling
                    console.log('No progress data yet, continuing to poll...');
                    setTimeout(pollProgress, 800);
                }
            })
            .catch(error => {
                console.error('Progress polling error:', error);
                const progressText = document.getElementById('progress-text');
                if (progressText) {
                    progressText.textContent = 'Checking progress...';
                }
                // Retry more frequently on errors during active import
                setTimeout(pollProgress, 1000);
            });
        }

        // Handle modal close with warning if import is in progress
        function handleModalClose() {
            if (isImportInProgress) {
                // Show warning popup
                const closeBtn = document.querySelector('button[onclick="handleModalClose()"]');
                showCloseWarning(closeBtn);
                return false;
            }
            
            // If import is completed, act like DONE button
            if (importCompleted) {
                // Show success message briefly
                showSuccessMessage('Import completed successfully!');
                
                // Update X button area to show loading (disable DONE button too)
                const doneBtn = document.getElementById('done-btn');
                if (doneBtn) {
                    doneBtn.textContent = 'Closing...';
                    doneBtn.disabled = true;
                    doneBtn.classList.remove('pulse');
                }
                
                // Show loading message in the modal
                const progressText = document.getElementById('progress-text');
                if (progressText) {
                    progressText.textContent = 'Refreshing page...';
                }
                
                // Keep modal open and reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                
                return;
            }
            
            // Normal close - no import was done, just close modal
            resetImportState();
            closeImportModal();
        }

        // Show warning popup when trying to close during import
        function showCloseWarning(closeBtn) {
            // Remove any existing warning
            const existingWarning = document.getElementById('close-warning');
            if (existingWarning) {
                existingWarning.remove();
            }

            // Create warning popup
            const warning = document.createElement('div');
            warning.id = 'close-warning';
            warning.className = 'fixed bg-yellow-100 border border-yellow-400 text-yellow-800 px-3 py-2 rounded-lg shadow-lg text-sm';
            warning.style.cssText = 'top: 20px; right: 80px; white-space: nowrap; z-index: 9999;';
            warning.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Wait until upload is done!';

            // Attach to body instead of button to avoid clipping
            document.body.appendChild(warning);

            // Auto-hide after 3 seconds
            setTimeout(() => {
                if (warning && warning.parentNode) {
                    warning.remove();
                }
            }, 3000);
        }

        // Handle completion when DONE button is clicked
        function handleImportComplete() {
            if (!importCompleted) {
                return; // Shouldn't happen since button is disabled
            }

            // Show success message briefly
            showSuccessMessage('Import completed successfully!');
            
            // Update DONE button to show loading state
            const doneBtn = document.getElementById('done-btn');
            if (doneBtn) {
                doneBtn.textContent = 'Reloading...';
                doneBtn.disabled = true;
                doneBtn.classList.remove('pulse');
            }
            
            // Show loading message in the modal
            const progressText = document.getElementById('progress-text');
            if (progressText) {
                progressText.textContent = 'Refreshing page...';
            }
            
            // Keep modal open and reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        // Reset import states
        function resetImportState() {
            isImportInProgress = false;
            importCompleted = false;
            displayedLogCount = 0;
            
            // Reset button states
            const uploadBtn = document.getElementById('upload-btn');
            const doneBtn = document.getElementById('done-btn');
            
            if (uploadBtn) {
                uploadBtn.style.display = 'inline-block';
            }
            if (doneBtn) {
                doneBtn.style.display = 'none';
                doneBtn.disabled = true;
                doneBtn.textContent = 'DONE';
            }
            
            // Reset modal size back to small
            const modalContainer = document.getElementById('modal-container');
            if (modalContainer) {
                modalContainer.classList.remove('max-w-6xl');
                modalContainer.classList.add('max-w-lg');
                modalContainer.style.height = 'auto'; // Reset to auto height
                modalContainer.style.maxHeight = 'none';
                modalContainer.style.minHeight = 'auto';
                modalContainer.style.padding = ''; // Reset padding
                console.log('Modal size reset to small');
            }
            
            // Restore original file input
            const form = document.getElementById('import-form');
            if (form) {
                const fileInput = form.querySelector('input[type="file"]');
                const uploadMessage = form.querySelector('div.bg-blue-50');
                if (fileInput && uploadMessage) {
                    fileInput.style.display = 'block';
                    uploadMessage.remove();
                }
            }
            
            // Hide progress and log containers
            const progressContainer = document.getElementById('progress-container');
            const transparencyLogContainer = document.getElementById('transparency-log-container');
            if (progressContainer) progressContainer.classList.add('hidden');
            if (transparencyLogContainer) transparencyLogContainer.classList.add('hidden');
        }

        // Show success message
        function showSuccessMessage(message) {
            // Create and show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            successDiv.textContent = message;
            document.body.appendChild(successDiv);

            // Auto-remove after 2 seconds
            setTimeout(() => {
                if (successDiv && successDiv.parentNode) {
                    successDiv.remove();
                }
            }, 2000);
        }

    </script>

</x-superadmin-layout>
