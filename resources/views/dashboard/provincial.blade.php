<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Provincial Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-6">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            {{-- Total Employees in Province --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Employees in Province</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="total-employees">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- FETS Requests (Submitted) --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">FETS Requests</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="fets-requests">{{ $fetsRequests }}</h3>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-file-import text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- FETS Verified --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">FETS Verified</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="fets-verified">{{ $fetsVerified }}</h3>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inventory Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- My Inventory Units --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">My Inventory Units</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="my-inventory">{{ $myInventoryCount }}</h3>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-box text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- All Employee Units --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">All Employee Units</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="employee-inventory">{{ $employeeInventoryCount }}</h3>
                    </div>
                    <div class="bg-teal-100 rounded-full p-3">
                        <i class="fas fa-boxes text-teal-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            {{-- Units to Return from Repair --}}
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Units for Repair Return</p>
                        <h3 class="text-3xl font-bold text-gray-800 mt-2" id="units-repair">{{ $unitsForRepair }}</h3>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-tools text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- FETS Status Distribution --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">FETS Status Distribution ({{ $province }})</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="fets-status">
                @foreach($fetsByStatus as $status)
                    <div class="text-center p-4 rounded-lg {{ 
                        $status->status === 'submitted' ? 'bg-yellow-100' : 
                        ($status->status === 'verified' ? 'bg-blue-100' : 
                        ($status->status === 'approved' ? 'bg-green-100' : 
                        ($status->status === 'completed' ? 'bg-gray-100' : 'bg-red-100')))
                    }}">
                        <p class="text-2xl font-bold text-gray-800">{{ $status->count }}</p>
                        <p class="text-sm text-gray-600 capitalize">{{ $status->status }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent FETS --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent FETS from {{ $province }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="recent-fets">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">FETS ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer Movement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentFets as $fets)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-blue-600">#{{ $fets->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $fets->submitter->fullname ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $fets->transfer_movement }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full {{ 
                                        $fets->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : 
                                        ($fets->status === 'verified' ? 'bg-blue-100 text-blue-800' : 
                                        ($fets->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                        ($fets->status === 'completed' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800')))
                                    }}">
                                        {{ ucfirst($fets->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $fets->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Auto-refresh script --}}
    <script>
        // Auto-refresh dashboard data every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000); // 30 seconds
    </script>
</x-app-layout>
