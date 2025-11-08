{{-- Places Management Tab Content --}}
<div class="space-y-6">
    {{-- Add New Location Section --}}
    <div class="bg-white shadow rounded-xl p-6">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
            Add New Location
        </h3>
        
        <div class="flex gap-4 items-end flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Category</label>
                <select id="locationCategory" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Category --</option>
                    <option value="province">Province</option>
                    <option value="municipality">Municipality (with Office)</option>
                    <option value="office">Office</option>
                </select>
            </div>
            
            <button onclick="showAddModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-1"></i> Add
            </button>
        </div>
    </div>

    {{-- Location Hierarchy Table --}}
    <div class="bg-white shadow rounded-xl p-6">
        <h3 class="text-xl font-semibold mb-4">Location Hierarchy</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-[#2e3192]">
                        <th class="p-3 text-left border border-gray-300 font-semibold text-white">Province</th>
                        <th class="p-3 text-left border border-gray-300 font-semibold text-white">Municipality</th>
                        <th class="p-3 text-left border border-gray-300 font-semibold text-white">Office</th>
                        <th class="p-3 text-center border border-gray-300 font-semibold w-32 text-white">Action</th>
                    </tr>
                </thead>
                <tbody id="placesTableBody">
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading locations...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Province Modal --}}
<div id="addProvinceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Add New Province</h3>
        <form action="{{ route('places.province.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Province Name</label>
                <input type="text" name="name" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter province name">
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModal('addProvinceModal')" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Add Province
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Municipality Modal --}}
<div id="addMunicipalityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Add New Municipality</h3>
        <form action="{{ route('places.municipality.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Province</label>
                <select name="parent_id" id="municipalityProvinceSelect" required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Province --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Municipality Name</label>
                <input type="text" name="name" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter municipality name">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Office Name</label>
                <input type="text" name="office_name" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter office name under this municipality">
                <p class="mt-1 text-xs text-gray-500">An office will be created under this municipality</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModal('addMunicipalityModal')" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Add Municipality
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Office Modal --}}
<div id="addOfficeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-96 shadow-2xl">
        <h3 class="text-xl font-bold mb-4 text-gray-800">Add New Office</h3>
        <form action="{{ route('places.office.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Province</label>
                <select id="officeProvinceSelect" onchange="loadMunicipalitiesForOffice()" required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Province --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Municipality</label>
                <select name="parent_id" id="officeMunicipalitySelect" required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Municipality --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Office Name</label>
                <input type="text" name="name" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter office name">
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModal('addOfficeModal')" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Add Office
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let placesData = [];

function showAddModal() {
    const category = document.getElementById('locationCategory').value;
    if (!category) { alert('Please select a category first'); return; }
    if (category === 'province') { showModal('addProvinceModal'); } 
    else if (category === 'municipality') { loadProvinces(); showModal('addMunicipalityModal'); }
    else if (category === 'office') { loadProvincesForOffice(); showModal('addOfficeModal'); }
}

function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
}

function loadProvinces() {
    fetch('/api/places/provinces').then(r => r.json()).then(data => {
        const select = document.getElementById('municipalityProvinceSelect');
        select.innerHTML = '<option value="">-- Select Province --</option>';
        data.forEach(p => { select.innerHTML += `<option value="${p.id}">${p.name}</option>`; });
    });
}

function loadProvincesForOffice() {
    fetch('/api/places/provinces').then(r => r.json()).then(data => {
        const select = document.getElementById('officeProvinceSelect');
        select.innerHTML = '<option value="">-- Select Province --</option>';
        data.forEach(p => { select.innerHTML += `<option value="${p.id}">${p.name}</option>`; });
    });
}

function loadMunicipalitiesForOffice() {
    const provinceId = document.getElementById('officeProvinceSelect').value;
    const munSelect = document.getElementById('officeMunicipalitySelect');
    
    if (!provinceId) {
        munSelect.innerHTML = '<option value="">-- Select Municipality --</option>';
        return;
    }
    
    fetch('/api/places/municipalities').then(r => r.json()).then(data => {
        munSelect.innerHTML = '<option value="">-- Select Municipality --</option>';
        data.filter(m => m.parent_id == provinceId).forEach(m => {
            munSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
        });
    });
}

function loadPlacesHierarchy() {
    fetch('/api/places/hierarchy').then(r => r.json()).then(data => {
        placesData = data;
        renderPlacesTable();
    }).catch(error => {
        console.error('Error:', error);
        document.getElementById('placesTableBody').innerHTML = '<tr><td colspan="4" class="p-8 text-center text-red-500">Error loading locations</td></tr>';
    });
}

function renderPlacesTable() {
    const tbody = document.getElementById('placesTableBody');
    if (placesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-gray-500">No locations found</td></tr>';
        return;
    }
    let html = '';
    placesData.forEach(province => {
        const municipalities = province.municipalities || [];
        
        // Calculate total row count for province (sum of all office rows across all municipalities)
        let totalProvinceRows = 0;
        municipalities.forEach(mun => {
            const officeCount = mun.offices ? mun.offices.length : 0;
            totalProvinceRows += Math.max(officeCount, 1); // At least 1 row per municipality
        });
        
        if (municipalities.length === 0) {
            html += `<tr class="hover:bg-gray-50">
                <td class="p-3 border border-gray-300"><span class="font-medium text-blue-600">${province.name}</span></td>
                <td class="p-3 border border-gray-300 text-gray-400 italic">No municipalities</td>
                <td class="p-3 border border-gray-300"></td>
                <td class="p-3 border border-gray-300 text-center">
                    <button onclick="deletePlace(${province.id}, 'province', '${province.name}')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        } else {
            let firstRow = true;
            let firstMunRow = true;
            municipalities.forEach((mun, mIdx) => {
                const offices = mun.offices || [];
                const oCount = offices.length;
                
                if (oCount === 0) {
                    html += `<tr class="hover:bg-gray-50">
                        ${firstRow ? `<td class="p-3 border border-gray-300" rowspan="${totalProvinceRows}">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-blue-600">${province.name}</span>
                                <button onclick="deletePlace(${province.id}, 'province', '${province.name}')" class="text-red-600 hover:text-red-800 ml-2" title="Delete Province">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>` : ''}
                        <td class="p-3 border border-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-green-600">${mun.name}</span>
                                <button onclick="deletePlace(${mun.id}, 'municipality', '${mun.name}')" class="text-red-600 hover:text-red-800 ml-2" title="Delete Municipality">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <td class="p-3 border border-gray-300 text-gray-400 italic">No offices</td>
                        <td class="p-3 border border-gray-300 text-center">-</td>
                    </tr>`;
                    firstRow = false;
                } else {
                    offices.forEach((office, oIdx) => {
                        html += `<tr class="hover:bg-gray-50">
                            ${firstRow ? `<td class="p-3 border border-gray-300" rowspan="${totalProvinceRows}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-blue-600">${province.name}</span>
                                    <button onclick="deletePlace(${province.id}, 'province', '${province.name}')" class="text-red-600 hover:text-red-800 ml-2" title="Delete Province">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>` : ''}
                            ${oIdx === 0 ? `<td class="p-3 border border-gray-300" rowspan="${oCount}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-green-600">${mun.name}</span>
                                    <button onclick="deletePlace(${mun.id}, 'municipality', '${mun.name}')" class="text-red-600 hover:text-red-800 ml-2" title="Delete Municipality">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>` : ''}
                            <td class="p-3 border border-gray-300"><span class="text-gray-700">${office.name}</span></td>
                            <td class="p-3 border border-gray-300 text-center">
                                <button onclick="deletePlace(${office.id}, 'office', '${office.name}')" class="text-red-600 hover:text-red-800" title="Delete Office">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                        firstRow = false;
                    });
                }
            });
        }
    });
    tbody.innerHTML = html;
}

function deletePlace(id, type, name) {
    if (!confirm(`Are you sure you want to delete this ${type}: ${name}?\n\nNote: Deleting will also remove all child locations.`)) return;
    fetch(`/api/places/${id}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(data => {
        if (data.success) { 
            // Update URL to maintain tab state on manual refresh
            const url = new URL(window.location);
            url.searchParams.set('tab', 'places');
            window.history.pushState({}, '', url);
            
            // Show success notification
            showNotification(data.message, 'success');
            
            // Reload the table
            loadPlacesHierarchy(); 
        } 
        else { 
            showNotification('Error: ' + data.message, 'error'); 
        }
    }).catch(error => { 
        console.error('Error:', error); 
        showNotification('An error occurred while deleting', 'error'); 
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 hover:opacity-75">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('placesTableBody')) { loadPlacesHierarchy(); }
});

if (typeof window.placeTabActivated === 'undefined') {
    window.placeTabActivated = function() { loadPlacesHierarchy(); };
}
</script>
