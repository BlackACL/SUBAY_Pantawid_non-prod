<x-ProvincialAdmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Inventory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
    <form method="GET" action="{{ route(Route::currentRouteName()) }}" class="mb-4 flex flex-wrap items-center gap-4">
        <label class="flex items-center">
            <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}>
            <span class="ml-2 text-sm">Show All Equipment</span>
        </label>

        <div>
            <input type="text" name="receiver" list="receiver-list" value="{{ request('receiver') }}"
                placeholder="Filter by Receiver"
                class="border-gray-300 rounded-md shadow-sm text-sm p-2">
            <datalist id="receiver-list">
                @foreach ($receivers as $receiver)
                    <option value="{{ $receiver }}">
                @endforeach
            </datalist>
        </div>

        <div>
            <input type="text" name="office" list="office-list" value="{{ request('office') }}"
                placeholder="Filter by Office"
                class="border-gray-300 rounded-md shadow-sm text-sm p-2">
            <datalist id="office-list">
                @foreach ($offices as $office)
                    <option value="{{ $office }}">
                @endforeach
            </datalist>
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


    <div class="overflow-x-auto">
        <table class="table-auto w-full border text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2 border">PROPERTY NO</th>
                    <th class="px-4 py-2 border">DESCRIPTION</th>
                    <th class="px-4 py-2 border">SERIAL</th>
                    <th class="px-4 py-2 border">RECEIVER</th>
                    <th class="px-4 py-2 border">OFFICE</th>
                    <th class="px-4 py-2 border">STATUS</th>
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
                        <td class="border px-4 py-2">{{ $item->PROPERTY_STATUS }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $inventory->links() }}
    </div>
</div>

            </div>
        </div>
    </div>
</x-ProvincialAdmin-layout>