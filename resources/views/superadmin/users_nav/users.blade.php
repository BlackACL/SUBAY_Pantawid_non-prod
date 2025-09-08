<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    @if(session('success'))
        <div id="success-alert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            <button onclick="document.getElementById('success-alert').style.display='none'" class="absolute top-0 right-0 mt-2 mr-4 text-green-700 hover:text-green-900 text-2xl font-bold leading-none focus:outline-none" aria-label="Close">
                &times;
            </button>
        </div>
    @endif
    @if(session('error'))
        <div id="error-alert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative flex items-center justify-between" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
                <button onclick="document.getElementById('error-alert').style.display='none'" class="absolute top-0 right-0 mt-2 mr-4 text-red-700 hover:text-red-900 text-2xl font-bold leading-none focus:outline-none" aria-label="Close">
                    &times;
                </button>
        </div>
    @endif

    <div class="py-6" id="main-content">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mt-4 mb-4">
                <!-- Search Filter -->
                <div class="max-w-md">
                    <form method="GET" action="{{ route('users') }}" class="flex gap-2">
                        <div class="relative w-96">
                            <input type="text" name="search" id="searchInput" placeholder="Search by Company ID or Full Name..." 
                                   value="{{ request('search') }}"
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('users') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
                
<!-- Add User / Officials Buttons -->
<div class="ml-4 flex gap-2">
    <!-- Add User Button -->
    <button onclick="openModal()" 
        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
        <i class="fas fa-user-plus"></i>
        {{ __('Add User') }}
    </button>

    <!-- Import Profiles -->
    <button onclick="openImportModal()" 
        class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded-lg transition">
        <i class="fas fa-file-upload"></i> {{ __('Import Profiles') }}
    </button>

    <!-- Add Official Button (links to index.blade.php for officials) -->
    <a href="{{ route('officials.index') }}" 
        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-1">
        <i class="fas fa-user-shield"></i>
        {{ __('Modify Official') }}
    </a>
</div>



            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Company ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Office</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->company_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="openUserProfile({{ $user->id }})" class="text-black hover:text-blue-800 hover:underline font-medium cursor-pointer">
                                        {{ $user->fullname }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->office }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button onclick="openArchiveModal({{ $user->id }}, '{{ $user->fullname }}')" class="bg-red-700 hover:bg-red-800 text-white font-bold py-1 px-4 rounded">Archive</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $users->links() }}
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
                        <!-- Full Name -->
                        <div class="mb-4">
                            <label for="fullname" class="block font-medium">Full Name</label>
                            <input type="text" name="fullname" id="fullname" class="w-full border rounded px-3 py-2" required>
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
                                <select name="municipality" id="municipality" class="w-full border rounded px-3 py-2" required>
                                    <option value="">Select Municipality</option>
                                </select>
                            </div>
                        </div>
                        <!-- Office -->
                        <div class="mb-4">
                            <label for="office" class="block font-medium">Office</label>
                            <select name="office" id="office" class="w-full border rounded px-3 py-2" required>
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
                            <div>
                                <label for="deleted_status" class="block font-medium">Deleted Status</label>
                                <select name="deleted_status" id="deleted_status" class="w-full border rounded px-3 py-2">
                                    <option value="No">Active</option>
                                    <option value="Yes">Deleted</option>
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
        <div id="import-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6 relative">
                <!-- Header -->
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-900">Import Employee Profiles</h3>
                    <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <!-- Body -->
                <div class="mt-4">
                    <form id="import-form" method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data" onsubmit="uploadCSV(event)">
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
                        
                        <!-- Footer -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                class="mt-4 bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-6 rounded-lg">
                                Upload
                            </button>
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
                <div class="flex items-center justify-between p-6 bg-[#ee1c25] text-white rounded-t-lg">
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

    <script>
        function openModal() {
            document.getElementById('modal-overlay').classList.remove('hidden');
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('main-content').classList.add('blur-md');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.add('hidden');
            document.getElementById('modal').classList.add('hidden');
            document.getElementById('main-content').classList.remove('blur-md');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // User Profile Modal Functions
        function openUserProfile(userId) {
            // Show loading state
            document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading profile...</p></div>';
            
            // Show modal
            document.getElementById('user-profile-overlay').classList.remove('hidden');
            document.getElementById('user-profile-modal').classList.remove('hidden');
            document.getElementById('main-content').classList.add('blur-md');

            // Fetch user data
            fetch(`/users/${userId}/profile`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('user-profile-content').innerHTML = generateUserProfileHTML(data.user);
                    } else {
                        document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('user-profile-content').innerHTML = '<div class="text-center py-8 text-red-600">Error loading user profile</div>';
                });
        }

        function closeUserProfile() {
            document.getElementById('user-profile-overlay').classList.add('hidden');
            document.getElementById('user-profile-modal').classList.add('hidden');
            document.getElementById('main-content').classList.remove('blur-md');
        }

        function generateUserProfileHTML(user) {
            return `
                <div>
                    <!-- Top Row: Avatar and User Info -->
                    <div class="flex items-start space-x-6 mb-6">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <!-- User Info -->
                        <div>
                            <h2 class="text-2xl font-bold text-[#2e3192] mb-1">${user.fullname}</h2>
                            <p class="text-gray-600 mb-1">${user.company_id}</p>
                            <p class="text-black font-medium mb-4">${user.access_level}</p>
                        </div>
                    </div>
                    <hr class="my-4 border-black border-t-2">
                    <!-- Bottom Row: Two Columns of Fields -->
                    <div class="flex w-full">
                        <!-- Left Column -->
                        <div class="space-y-3 flex-1">
                            <div>
                                <span class="font-bold text-black">Full Name:</span>
                                <span class="ml-2 text-gray-900">${user.fullname}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Email:</span>
                                <span class="ml-2 text-gray-900">${user.email}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Username:</span>
                                <span class="ml-2 text-gray-900">${user.username}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Employee Status:</span>
                                <span class="ml-2 text-gray-900">${user.employee_status}</span>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="space-y-3 flex-1">
                            <div>
                                <span class="font-bold text-black">Region:</span>
                                <span class="ml-2 text-gray-900">${user.region}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Province:</span>
                                <span class="ml-2 text-gray-900">${user.province}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Municipality:</span>
                                <span class="ml-2 text-gray-900">${user.municipality}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Office:</span>
                                <span class="ml-2 text-gray-900">${user.office}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Close user profile modal when clicking outside
        document.getElementById('user-profile-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserProfile();
            }
        });

        // Close user profile modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserProfile();
                closeArchiveModal();
            }
        });

        // Archive Modal Functions
        function openArchiveModal(userId, userName) {
            // Set the user name in the modal
            document.getElementById('archive-user-name').textContent = userName;
            
            // Set the form action
            document.getElementById('archive-form').action = `/users/${userId}/archive`;
            
            // Show modal
            document.getElementById('archive-modal-overlay').classList.remove('hidden');
            document.getElementById('archive-modal').classList.remove('hidden');
            document.getElementById('main-content').classList.add('blur-md');
        }

        function closeArchiveModal() {
            document.getElementById('archive-modal-overlay').classList.add('hidden');
            document.getElementById('archive-modal').classList.add('hidden');
            document.getElementById('main-content').classList.remove('blur-md');
        }

        // Close archive modal when clicking outside
        document.getElementById('archive-modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeArchiveModal();
            }
        });

        // Province → Municipality mapping
        const provinceMunicipalityMap = {
            "DAVAO CITY": ["Davao City"], // special case: only one municipality
            "DAVAO OCCIDENTAL": [
                "DON MARCELINO",
                "JOSE ABAD SANTOS (TRINIDAD)",
                "MALITA",
                "SANTA MARIA",
                "SARANGANI"
            ],
            "DAVAO DE ORO": [
                "MONKAYO",
                "COMPOSTELA",
                "MONTEVISTA",
                "NEW BATAAN",
                "MARAGUSAN (SAN MARIANO)",
                "NABUNTURAN (Capital)",
                "MAWAB",
                "MACO",
                "PANTUKAN",
                "MABINI (DOÑA ALICIA)",
                "LAAK (SAN VICENTE)"
            ],
            "DAVAO DEL NORTE": [
                "ASUNCION (SAUG)",
                "BRAULIO E. DUJALI",
                "CARMEN",
                "KAPALONG",
                "NEW CORELLA",
                "SAN ISIDRO",
                "SANTO TOMAS",
                "TALAINGOD",
                "CITY OF TAGUM (Capital)",
                "CITY OF PANABO",
                "ISLAND GARDEN CITY OF SAMAL"
            ],
            "DAVAO DEL SUR": [
                "BANSALAN",
                "HAGONOY",
                "KIBLAWAN",
                "MAGSAYSAY",
                "MALALAG",
                "MATANAO",
                "PADADA",
                "SANTA CRUZ",
                "CITY OF DIGOS (Capital)",
                "SULOP"
            ],
            "DAVAO ORIENTAL": [
                "BAGANGA",
                "BANAYBANAY",
                "BOSTON",
                "CARAGA",
                "CATEEL",
                "GOVERNOR GENEROSO",
                "LUPON",
                "MANAY",
                "CITY OF MATI (Capital)",
                "SAN ISIDRO",
                "TARRAGONA"
            ]
        };

        // Municipality → Offices mapping
        const officeMap = {
            // DAVAO CITY
            "Davao City": [
                "Paquibato Sub-District",
                "Talomo A Sub-District",
                "Talomo B Sub-District",
                "Toril A Sub-District",
                "Toril B Sub-District",
                "Buhangin A Sub-District",
                "Buhangin B Sub-District",
                "Poblacion Sub-District",
                "Agdao Sub-District",
                "Bunawan Sub-District",
                "Calinan Sub-District",
                "Baguio Sub-District",
                "Tugbok Sub-District",
                "Marilog Sub-District"
            ],

            // DAVAO DE ORO
            "MONKAYO": ["Monkayo Municipal Operations Office"],
            "COMPOSTELA": ["Compostela Municipal Operation Office"],
            "MONTEVISTA": ["Montevista Municipal Operations Office"],
            "NEW BATAAN": ["New Bataan Municipal Operations Office"],
            "MARAGUSAN (SAN MARIANO)": ["Maragusan Municipal Operations Office"],
            "NABUNTURAN (Capital)": ["Nabunturan Municipal Operations Office"],
            "MAWAB": ["Mawab Municipal Operations Office"],
            "MACO": ["Maco Municipal Operations Office"],
            "PANTUKAN": ["Pantukan Municipal Operations Office"],
            "MABINI (DOÑA ALICIA)": ["Mabini Municipal Operations Office"],
            "LAAK (SAN VICENTE)": ["Laak Municipal Operations Office"],

            // DAVAO ORIENTAL
            "BAGANGA": ["Baganga Municipal Operations Office"],
            "BANAYBANAY": ["Banaybanay Municipal Operations Office"],
            "BOSTON": ["Boston Municipal Operations Office"],
            "CARAGA": ["Caraga Municipal Operations Office"],
            "CATEEL": ["Cateel Municipal Operations Office"],
            "GOVERNOR GENEROSO": ["Governor Generoso Municipal Operations Office"],
            "LUPON": ["Lupon Municipal Operations Office"],
            "MANAY": ["Manay Municipal Operations Office"],
            "CITY OF MATI (Capital)": ["Mati City Operations Office"],
            "SAN ISIDRO": ["San Isidro Municipal Operations Office"],
            "TARRAGONA": ["Tarragona Municipal Operations Office"],

            // DAVAO DEL NORTE
            "ASUNCION (SAUG)": ["Asuncion Municipal Operations Office"],
            "BRAULIO E. DUJALI": ["Braulio E. Dujali Municipal Operations Office"],
            "CARMEN": ["Carmen Municipal Operations Office"],
            "KAPALONG": ["Kapalong Municipal Operations Office"],
            "NEW CORELLA": ["New Corella Municipal Operations Office"],
            "SAN ISIDRO": ["San Isidro Municipal Operations Office"],
            "SANTO TOMAS": ["Santo Tomas Municipal Operations Office"],
            "TALAINGOD": ["Talaingod Municipal Operations Office"],
            "CITY OF TAGUM (Capital)": ["Tagum City Operations Office"],
            "CITY OF PANABO": ["Panabo City Operations Office"],
            "ISLAND GARDEN CITY OF SAMAL": ["Island Garden City of Samal City Operations Office"],

            // DAVAO OCCIDENTAL
            "DON MARCELINO": ["Don Marcelino Municipal Operations Office"],
            "JOSE ABAD SANTOS (TRINIDAD)": ["Jose Abad Santos Municipal Operations Office"],
            "MALITA": ["Malita Municipal Operations Office"],
            "SANTA MARIA": ["Santa Maria Municipal Operations Office"],
            "SARANGANI": ["Sarangani Municipal Operations Office"],

            // DAVAO DEL SUR
            "BANSALAN": ["Bansalan Municipal Operations Office"],
            "HAGONOY": ["Hagonoy Municipal Operations Office"],
            "KIBLAWAN": ["Kiblawan Municipal Operations Office"],
            "MAGSAYSAY": ["Magsaysay Municipal Operations Office"],
            "MALALAG": ["Malalag Municipal Operations Office"],
            "MATANAO": ["Matanao Municipal Operations Office"],
            "PADADA": ["Padada Municipal Operations Office"],
            "SANTA CRUZ": ["Sta. Cruz Municipal Operations Office"],
            "CITY OF DIGOS (Capital)": ["Digos City Operations Office"],
            "SULOP": ["Sulop Municipal Operations Office"]
        };

        // Select elements
        const provinceSelect = document.getElementById('province');
        const municipalitySelect = document.getElementById('municipality');
        const officeSelect = document.getElementById('office');

        provinceSelect.addEventListener('change', function() {
            const selectedProvince = this.value;
            municipalitySelect.innerHTML = '<option value="">Select Municipality</option>';
            officeSelect.innerHTML = '<option value="">Select Office</option>';

            if (provinceMunicipalityMap[selectedProvince]) {
                const municipalities = [...provinceMunicipalityMap[selectedProvince]];

                municipalities.forEach(muni => {
                    const option = document.createElement('option');
                    option.value = muni;
                    option.textContent = muni;
                    municipalitySelect.appendChild(option);
                });

                // Special case: DAVAO CITY auto-select
                if (selectedProvince === "DAVAO CITY") {
                    municipalitySelect.value = "Davao City";
                    loadOffices("Davao City");
                }
            }
        });

        municipalitySelect.addEventListener('change', function() {
            loadOffices(this.value);
        });

        function loadOffices(municipality) {
            officeSelect.innerHTML = '<option value="">Select Office</option>';
            if (officeMap[municipality]) {
                officeMap[municipality].forEach(off => {
                    const option = document.createElement('option');
                    option.value = off;
                    option.textContent = off;
                    officeSelect.appendChild(option);
                });
            }
        }

        // Auto-open modal if there are validation errors
        @if(session('openModal') || $errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                openModal();
            });
        @endif

        // Import Profiles Modal Script
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

        function uploadCSV(event) {
            event.preventDefault();

            const form = document.getElementById('import-form');
            const formData = new FormData(form);
            const progressBar = document.getElementById('progress-bar');
            const progressContainer = document.getElementById('progress-container');
            const progressText = document.getElementById('progress-text');

            progressContainer.classList.remove('hidden');
            progressBar.style.width = "0%";
            progressText.innerText = "0%";

            const xhr = new XMLHttpRequest();
            xhr.open("POST", form.action, true);
            xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector('input[name="_token"]').value);

            // 🚀 Do NOT use xhr.upload progress anymore (remove it!)
            // Just poll server progress
            startPolling();

            // On success
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    let response = {};
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (e) {
                        console.error("Invalid JSON", xhr.responseText);
                    }

                    progressBar.classList.remove("bg-blue-600");
                    progressBar.classList.add("bg-green-600");

                    if (response.skipped && response.skipped.length > 0) {
                        alert("Skipped rows:\n" + response.skipped.join("\n"));
                    }

                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    progressBar.classList.remove("bg-blue-600");
                    progressBar.classList.add("bg-red-600");
                    progressText.innerText = "Upload Failed";
                }
            };

            xhr.onerror = function () {
                progressBar.classList.remove("bg-blue-600");
                progressBar.classList.add("bg-red-600");
                progressText.innerText = "Error uploading file";
            };

            xhr.send(formData);
        }

        async function startPolling() {
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');

            const interval = setInterval(async () => {
                const res = await fetch('/import/progress');
                const data = await res.json();

                if (data) {
                    const percent = Math.round((data.processed / data.total) * 100);

                    progressBar.style.width = percent + '%';
                    progressText.innerText = percent + '%';

                    if (percent >= 100) {
                        clearInterval(interval);

                        // Smooth fade-in for done text
                        progressText.classList.add("opacity-0"); // fade out current % text
                        setTimeout(() => {
                            progressText.innerText = '100% ✓ Done!';
                            progressText.classList.remove("opacity-0");
                            progressText.classList.add("opacity-100");
                        }, 300);
                    }
                }
            }, 1000);
        }


    </script>

</x-superadmin-layout>