<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Officials Management') }}
        </h2>
    </x-slot>

    <div class="p-6" x-data="officialsHistory()">
        <div class="space-y-6">
            @php
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
                                            <span class="font-semibold text-green-700">
                                                {{ ($active->user_id && $active->user) ? $active->user->fullname : $active->fullname }}
                                            </span>
                                        @else
                                            <span class="text-gray-500 italic">No active official</span>
                                        @endif
                                    </td>
                                    <td class="p-2 space-x-2">
                                        <button class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition replace-btn"
                                            data-role="{{ $role }}"
                                            data-province="{{ $province ?? '' }}"
                                            data-active-id="{{ $active ? $active->id : '' }}">
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

        <div id="replaceModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-6 rounded-xl w-1/3">
                <h2 class="text-lg font-bold mb-4">Replace Official</h2>
                <form id="replaceForm" method="POST">
                    @csrf @method('POST')
                    <input type="hidden" name="role" id="replaceRole">
                    <input type="hidden" name="province" id="replaceProvince">

                    <div class="mb-4" id="replaceInputContainer">
                        </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeReplaceModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm Replace</button>
                    </div>
                </form>
            </div>
        </div>

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

            let container = document.getElementById('replaceInputContainer');
            container.innerHTML = '';

            // ✅ --- THIS IS THE FIX --- ✅
            // Define which roles should use a simple text input for the name.
            const textBasedRoles = ['Recommending', 'Approving', 'Head of Property'];

            if (textBasedRoles.includes(role)) {
                // This block is for TEXT-BASED roles. It creates an <input type="text">.
                let label = document.createElement('label');
                label.textContent = 'Full Name';
                label.classList.add('block','mb-1','text-sm','font-medium');
                container.appendChild(label);

                let input = document.createElement('input');
                input.name = 'fullname';
                input.type = 'text';
                input.required = true;
                input.classList.add('w-full','border','rounded','p-2');
                container.appendChild(input);

            } else {
                // This block is for USER-BASED roles (DPSCs). It creates a <select> dropdown.
                fetch(`/eligible-users?role=${encodeURIComponent(role)}&province=${encodeURIComponent(province)}`)
                    .then(res => res.json())
                    .then(users => {
                        if(users.length === 0){
                            let msg = document.createElement('p');
                            msg.textContent = 'No eligible users found for this role/province.';
                            msg.classList.add('text-red-600','italic');
                            container.appendChild(msg);
                            return;
                        }

                        let label = document.createElement('label');
                        label.textContent = 'Select User';
                        label.classList.add('block','mb-1','text-sm','font-medium');
                        container.appendChild(label);

                        let select = document.createElement('select');
                        select.name = 'user_id';
                        select.required = true;
                        select.classList.add('w-full','border','rounded','p-2');

                        users.forEach(user => {
                            let opt = document.createElement('option');
                            opt.value = user.id;
                            opt.textContent = `${user.fullname} (${user.province || 'No Province'})`;
                            select.appendChild(opt);
                        });

                        container.appendChild(select);
                    })
                    .catch(err => {
                        let msg = document.createElement('p');
                        msg.textContent = 'Error loading users.';
                        msg.classList.add('text-red-600','italic');
                        container.appendChild(msg);
                        console.error(err);
                    });
            }
        }

        function closeReplaceModal() {
            document.getElementById('replaceModal').classList.add('hidden');
        }

        function officialsHistory() {
            return {
                showHistory: false,
                selectedRole: '',
                selectedProvince: '',
                historyList: [],
                async loadHistory(role, province) {
                    this.selectedRole = role;
                    this.selectedProvince = province || 'National'; // Use 'National' if province is empty
                    this.showHistory = true;

                    let url = `/officials/history/${encodeURIComponent(role)}/${encodeURIComponent(province || '-')}`;
                    let res = await fetch(url);
                    this.historyList = await res.json();
                }
            }
        }

        // Add event listeners when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('replace-btn')) {
                    const role = e.target.dataset.role;
                    const province = e.target.dataset.province;
                    const activeId = e.target.dataset.activeId;
                    openReplaceModal(role, province, activeId || null);
                }
            });
        });
    </script>

    <style>[x-cloak] { display: none !important; }</style>
</x-superadmin-layout>
