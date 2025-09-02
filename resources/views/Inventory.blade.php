<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Inventory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

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
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ auth()->user()->fullname }}</h2>
                        <p class="text-gray-600 mb-1">{{ auth()->user()->company_id }}</p>
                        <p class="text-black font-medium mb-4">{{ auth()->user()->access_level }}</p>
                    </div>
                </div>

                <hr class="my-4 border-black border-t-2">

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

                <!-- ✅ Label for Inventory -->
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Inventory</h2>

                <!-- ✅ FILTER FORM -->
                <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by Description, Serial No, or Property No..."
                            class="border-gray-300 rounded-md shadow-sm text-sm p-2 w-96">
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>

                    @if(request('search'))
                        <a href="{{ route(Route::currentRouteName()) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded">
                            Clear
                        </a>
                    @endif
                </form>

                <!-- ✅ INVENTORY TABLE -->
                @if ($inventory->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border border-gray-300 text-sm">
                            <thead class="bg-gray-100 text-left">
                                <tr>
                                    <th class="px-4 py-2 border">PROPERTY NO</th>
                                    <th class="px-4 py-2 border">SERIAL NO</th>
                                    <th class="px-4 py-2 border">GENERAL DESCRIPTION</th>
                                    <th class="px-4 py-2 border">RECEIVER</th>
                                    <th class="px-4 py-2 border">OFFICE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventory as $item)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 border">{{ $item->PROPERTY_NO }}</td>
                                        <td class="px-4 py-2 border">{{ $item->SERIAL_NO }}</td>
                                        <td class="px-4 py-2 border">{{ $item->GENERAL_DESCRIPTION }}</td>
                                        <td class="px-4 py-2 border">{{ $item->RECEIVER }}</td>
                                        <td class="px-4 py-2 border">{{ $item->OFFICE }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ✅ PAGINATION -->
                    <div class="mt-4">
                        {{ $inventory->links() }}
                    </div>
                @else
                    <p class="text-gray-500">No equipment found for your account.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>