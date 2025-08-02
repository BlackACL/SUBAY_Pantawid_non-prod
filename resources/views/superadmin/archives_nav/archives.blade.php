<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Archives') }}
        </h2>
    </x-slot>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="py-12" id="main-content">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-between items-center mt-4 mb-4">
                <!-- Search Filter -->
                <div class="max-w-xs">
                    <form method="GET" action="{{ route('archives') }}" class="flex gap-2">
                        <div class="relative w-64">
                            <input type="text" name="search" id="searchInput" placeholder="Search by ID or Full Name..." 
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
                            <a href="{{ route('archives') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Company ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date Archived</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($archivedUsers as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="openUserProfile({{ $user->id }})" class="text-black hover:text-blue-800 hover:underline font-medium cursor-pointer">
                                    {{ $user->fullname }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                {{ $user->archived_at ? $user->archived_at->format('m/d/Y') : '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                No archived users found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $archivedUsers->links() }}
            </div>
        </div>
    </div>

    <!-- User Profile Modal Overlay -->
    <div id="user-profile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden">
        <!-- User Profile Modal -->
        <div id="user-profile-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto relative">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">User Profile</h3>
                    <button onclick="closeUserProfile()" class="text-gray-400 hover:text-gray-600">
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

    <script>
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
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">${user.fullname}</h2>
                            <p class="text-gray-600 mb-1">${user.id}</p>
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
            }
        });
    </script>
</x-superadmin-layout>
