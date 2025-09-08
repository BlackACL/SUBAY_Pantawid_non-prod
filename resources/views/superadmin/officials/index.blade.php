<x-superadmin-layout>
    <div class="p-6" x-data="officialsHistory()">
        <h1 class="text-2xl font-bold mb-6">Officials Management</h1>

        <div class="space-y-6">
            @php
                // Ensure roles always show up even if no entries exist yet
                $requiredRoles = ['Provincial DPSC', 'Regional DPSC', 'Head of Property', 'Recommending', 'Approving'];
            @endphp

            @foreach ($requiredRoles as $role)
                @php $byProvince = $officials[$role] ?? collect([null => collect()]); @endphp

                <div class="bg-white shadow rounded-xl p-4">
                    <h2 class="text-xl font-semibold mb-3">{{ $role }}</h2>

                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 text-left">Province</th>
                                <th class="p-2 text-left">Active Official</th>
                                <th class="p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($byProvince as $province => $group)
                                @php $active = $group->firstWhere('active', true); @endphp
                                <tr class="border-t">
                                    <td class="p-2">{{ $province ?? '-' }}</td>
                                    <td class="p-2">
                                        @if($active)
                                            <span class="font-semibold text-green-700">{{ $active->fullname }}</span>
                                        @else
                                            <span class="text-gray-500 italic">No active official</span>
                                        @endif
                                    </td>
                                    <td class="p-2 space-x-2">
                                        <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition"
                                            onclick="openReplaceModal('{{ $role }}', '{{ $province }}', '{{ $active->id ?? '' }}')">
                                            Replace
                                        </button>
                                        <button class="px-3 py-1 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition"
                                            @click="loadHistory('{{ $role }}', '{{ $province }}')">
                                            View History
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <!-- Replace Modal -->
        <div id="replaceModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-xl w-1/3">
                <h2 class="text-lg font-bold mb-4">Replace Official</h2>
                <form id="replaceForm" method="POST">
                    @csrf @method('POST')
                    <input type="hidden" name="role" id="replaceRole">
                    <input type="hidden" name="province" id="replaceProvince">

                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium">Full Name</label>
                        <input type="text" name="fullname" class="w-full border rounded p-2" required>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeReplaceModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm Replace</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Modal -->
        <div x-show="showHistory" x-cloak x-transition.opacity class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-xl w-1/2">
                <h2 class="text-xl font-bold mb-4">
                    History for <span x-text="selectedProvince"></span> - <span x-text="selectedRole"></span>
                </h2>

                <table class="w-full text-left border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2">Full Name</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Date Assigned</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="official in historyList" :key="official.id">
                            <tr class="border-t">
                                <td class="p-2" x-text="official.fullname"></td>
                                <td class="p-2">
                                    <span x-text="official.active ? 'Active' : 'Inactive'" 
                                          :class="official.active ? 'text-green-600 font-semibold' : 'text-gray-500'"></span>
                                </td>
                                <td class="p-2" x-text="new Date(official.created_at).toLocaleString()"></td>
                                <td class="p-2">
                                    <template x-if="!official.active">
                                        <form :action="`/officials/reactivate/${official.id}`" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                                                Reactivate
                                            </button>
                                        </form>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div class="flex justify-end mt-4">
                    <button @click="showHistory=false" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openReplaceModal(role, province, activeId) {
            document.getElementById('replaceModal').classList.remove('hidden');
            document.getElementById('replaceRole').value = role;
            document.getElementById('replaceProvince').value = province;
            document.getElementById('replaceForm').action = `/officials/update/${activeId}`;
        }
        function closeReplaceModal() { document.getElementById('replaceModal').classList.add('hidden'); }

        function officialsHistory() {
            return {
                showHistory: false,
                selectedRole: '',
                selectedProvince: '',
                historyList: [],
                async loadHistory(role, province) {
                    this.selectedRole = role;
                    this.selectedProvince = province;
                    this.showHistory = true;

                    let url = `/officials/history/${role}/${province}`;
                    let res = await fetch(url);
                    this.historyList = await res.json();
                }
            }
        }
    </script>

    <style>[x-cloak] { display: none !important; }</style>
</x-superadmin-layout>
