<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Inventory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <!-- ✅ FILTER FORM -->
                <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4">
                    <div>
                        <input type="text" name="description" value="{{ request('description') }}"
                            placeholder="Search Description"
                            class="border-gray-300 rounded-md shadow-sm text-sm p-2">
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
                        Apply Filters
                    </button>
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
