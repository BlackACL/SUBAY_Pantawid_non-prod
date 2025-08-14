<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight tracking-wide">
            {{ __('ACTIVITY LOGS') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#F6F8FA] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-gray-900">
                <div class="relative overflow-x-auto">
                    <form method="GET" action="{{ route('superadmin.logs_nav.logs') }}" class="flex items-center gap-3 mb-6 flex-wrap bg-white p-4 rounded-lg shadow">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search user/email/IP address"
                            class="border border-gray-300 rounded-lg px-17 py-2 text-sm text-left focus:ring-1 focus:ring-[#274C77] focus:outline-none transition"
                        >
                        <button type="submit" class="bg-[#274C77] hover:bg-[#193A5E] text-white px-3 py-2 rounded-lg text-sm font-semibold transition flex items-center" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('superadmin.logs_nav.logs') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold transition flex items-center" title="Clear">
                            <i class="fas fa-times"></i>
                        </a>
                    </form>
                    <div class="relative overflow-x-auto shadow-lg lg:rounded-lg bg-white">
                        <table class="min-w-full text-left rounded-lg overflow-hidden">
                            <thead class="text-xs font-semibold uppercase text-white bg-[#274C77]">
                                <tr>
                                    <th scope="col" class="px-6 py-4 w-[15%]">Date and Time</th>
                                    <th scope="col" class="px-6 py-4 w-[30%]">User</th>
                                    <th scope="col" class="px-6 py-4">Role</th>
                                    <th scope="col" class="px-6 py-4">Ip Address</th>
                                    <!-- <th scope="col" class="px-6 py-4 w-[15%]">Device</th> -->
                                    <th scope="col" class="px-6 py-4">Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($activities as $activity)
                                <tr class="border-b {{ $loop->even ? 'bg-[#F8F9FA]' : 'bg-white' }} text-black shadow-sm text-sm hover:bg-[#E3EAF3] transition">
                                    <td class="px-6 py-4 align-middle">
                                        <span class="font-medium">{{ \Carbon\Carbon::parse($activity->created_at)->format('m-d-Y') }}</span><br>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="font-bold text-[#274C77]">{{ optional($activity->causer)->fullname ?? optional($activity->causer)->name ?? 'System' }}</span>
                                        @if(optional($activity->causer)->email)
                                            <br>
                                            <span class="text-xs text-gray-500">{{ optional($activity->causer)->email }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        @php
                                            $role = $activity->properties['role'] ?? 'N/A';
                                            // Lighter color coding for roles
                                            if ($role === 'superadmin') {
                                                $roleBg = 'bg-[#DBEAFE] text-[#1E3A8A]';
                                            } elseif ($role === 'Regional DPSC') {
                                                $roleBg = 'bg-[#D1FAE5] text-[#047857]';
                                            } elseif ($role === 'Provincial DPSC') {
                                                $roleBg = 'bg-[#FEF3C7] text-[#F59E0B]';
                                            } elseif ($role === 'Employee') {
                                                $roleBg = 'bg-[#F3F4F6] text-[#6B7280]';
                                            } else {
                                                $roleBg = 'bg-gray-100 text-gray-900';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full {{ $roleBg }} font-semibold text-xs shadow-sm border border-gray-100">
                                            {{ $role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $activity->properties['ip'] ?? 'N/A' }}</span>
                                    </td>
                                    <!-- <td class="px-6 py-4 align-middle">
                                        {{ $activity->properties['device'] ?? 'N/A' }}
                                    </td> -->
                                    <td class="px-6 py-4 align-middle">
                                        @php
                                            $activityText = $activity->description;
                                            $iconClass = 'fas fa-file-alt text-gray-400'; // Default icon
                                            $lowerText = strtolower($activityText);

                                            if (str_contains($lowerText, 'login')) {
                                                $iconClass = 'fas fa-sign-in-alt text-green-600'; // Log in
                                            } elseif (str_contains($lowerText, 'logout')) {
                                                $iconClass = 'fas fa-sign-out-alt text-red-600'; // Log out
                                            } elseif (str_contains($lowerText, 'added')) {
                                                $iconClass = 'fas fa-user-plus text-blue-400';
                                            } elseif (str_contains($lowerText, 'submitted')) {
                                                $iconClass = 'fas fa-paper-plane text-indigo-400';
                                            } elseif (str_contains($lowerText, 'verified')) {
                                                $iconClass = 'fas fa-check-circle text-green-400';
                                            } elseif (str_contains($lowerText, 'approved')) {
                                                $iconClass = 'fas fa-thumbs-up text-yellow-400';
                                            } elseif (str_contains($lowerText, 'archived')) {
                                                $iconClass = 'fas fa-archive text-gray-400';
                                            } elseif (str_contains($lowerText, 'rejected')) {
                                                $iconClass = 'fas fa-times-circle text-red-400';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-2">
                                            <i class="{{ $iconClass }}"></i>
                                            <span class="text-gray-700">{{ $activityText }}</span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-gray-500">No activity logs found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div>
                            {{-- Pagination links --}}
                            <div class="mt-4 p-4 rounded-lg shadow bg-white flex justify-center">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-superadmin-layout>