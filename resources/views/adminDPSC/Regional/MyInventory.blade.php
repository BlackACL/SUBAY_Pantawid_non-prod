<x-RegionalAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventory') }}
        </h2>
    </x-slot>

    <div class="py-6" 
     x-data="{ showFetsApp: false, showSubmitted: false }"
     x-init="
        // Optional: Reset modal visibility on page load
        showFetsApp = false; 
        showSubmitted = false;
     "
     x-effect="
        if (showFetsApp || showSubmitted) { 
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        } else { 
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }
     ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- User Profile Section -->
                    <div class="flex items-start space-x-6 mb-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div>
                            <h2 class="text-2xl font-bold text-[#2e3192] mb-1">{{ auth()->user()->fullname }}</h2>
                            <p class="text-gray-600 mb-1">{{ auth()->user()->company_id }}</p>
                            <p class="text-black font-medium mb-4">
                                @if(auth()->user()->access_level === 'Provincial DPSC')
                                    Provincial DPSC - {{ strtoupper(auth()->user()->province ?? 'N/A') }}
                                @elseif(auth()->user()->access_level === 'Regional DPSC')
                                    Regional DPSC - {{ strtoupper(auth()->user()->region ?? 'N/A') }}
                                @elseif(auth()->user()->access_level === 'Superadmin')
                                    Superadmin
                                @else
                                    {{ auth()->user()->access_level }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ auth()->user()->fullname }}</h2>
                        <p class="text-gray-600 mb-1">{{ auth()->user()->company_id }}</p>
                        <p class="text-black font-medium mb-4">{{ auth()->user()->access_level }}</p>
                    </div>
                </div>

                    <!-- Bottom Row: Two Columns -->
                    <div class="flex w-full mb-6">
                        <!-- Left Column -->
                        <div class="space-y-3 flex-1">
                            <div>
                                <span class="font-bold text-black">Full Name:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->fullname }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Email:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->email }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Username:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->username }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Employee Status:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->employee_status }}</span>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-3 flex-1">
                            <div>
                                <span class="font-bold text-black">Region:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->region }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Province:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->province }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Municipality:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->municipality }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-black">Office:</span>
                                <span class="ml-2 text-gray-900">{{ auth()->user()->office }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                <div class="p-6 text-gray-900">
                    <!-- Label for Inventory -->
                    <h2 class="text-4xl font-bold text-gray-800 mb-4">INVENTORY</h2>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm">Show All Equipment</span>
                            </label>

                            <div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Search by Receiver, Description, Serial No, or Property No..."
                                    class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-96">
                            </div>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>

                            @if(request('search'))
                            <a href="{{ route(Route::currentRouteName()) }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                                Clear
                            </a>
                            @endif
                        </form>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                                <button 
                                    id="fetsButton"
                                    class="bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded"
                                >
                                    FETS Application
                                </button>
                        </div>
                    </div>

                        <div class="overflow-x-auto">
                            <table class="table-auto w-full border text-sm">
                                <thead class="bg-[#2e3192] text-center">
                                    <tr>
                                        <th class="px-4 py-2 text-white border">PROPERTY NO</th>
                                        <th class="px-4 py-2 text-white border">DESCRIPTION</th>
                                        <th class="px-4 py-2 text-white border">SERIAL</th>
                                        <th class="px-4 py-2 text-white border">RECEIVER</th>
                                        <th class="px-4 py-2 text-white border">OFFICE</th>
                                        <th class="px-4 py-2 text-white border">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventory as $item)
                                        <tr>
                                            <td class="border px-4 py-2">{{ $item->PROPERTY_NO }}</td>
                                            <td class="border px-4 py-2">{{ $item->GENERAL_DESCRIPTION }}</td>
                                            <td class="border px-4 py-2">{{ $item->SERIAL_NO }}</td>
                                            <td class="border px-4 py-2">{{ $item->RECEIVER }}</td>
                                            <td class="border px-4 py-2">{{ $item->OFFICE }}</td>
                                            <td class="border px-4 py-2">{{ $item->PAR_NO }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4">No data found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $inventory->links() }}
                    </div>
                </div>

            </div>
        </div>
        <!-- FETS Application Modal -->
        <div 
            id="fetsModal" 
            class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        >
            <div class="bg-white rounded-lg w-11/12 max-w-6xl h-[90vh] flex flex-col shadow-lg">
                
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">FETS Application</h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-hidden">
                    <iframe 
                        id="fetsIframe"
                        src=""
                        class="w-full h-full border-none"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</x-RegionalAdmin-layout>

<!-- Tailwind + JS -->
<script>
const fetsButton = document.getElementById('fetsButton');
const fetsModal = document.getElementById('fetsModal');
const closeModal = document.getElementById('closeModal');
const fetsIframe = document.getElementById('fetsIframe');

// Dynamic route for modal
const fetsRoute = "{{ route('fets.select') }}";

fetsButton.addEventListener('click', () => {
    fetsIframe.src = fetsRoute; // load correct page
    fetsModal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden'); // prevent background scroll
});

closeModal.addEventListener('click', () => {
    fetsModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    fetsIframe.src = ""; // clear iframe to stop video/audio if any
});

// Close modal on backdrop click
fetsModal.addEventListener('click', (e) => {
    if(e.target === fetsModal){
        fetsModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        fetsIframe.src = "";
    }
});
</script>