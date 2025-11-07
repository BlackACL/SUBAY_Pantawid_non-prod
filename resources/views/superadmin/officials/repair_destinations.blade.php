<div class="bg-white shadow rounded-xl p-6 mt-10">
    <h2 class="text-xl font-semibold mb-4">Modify Repair Destinations</h2>

    <form action="{{ route('repair-destinations.store') }}" method="POST" class="mb-6 flex flex-col sm:flex-row gap-2 items-start sm:items-end">
        @csrf
        <input type="text" name="name" placeholder="New Repair Destination" class="border px-3 py-2 rounded w-64 focus:ring focus:ring-blue-200" required>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">Add</button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border text-left">Name</th>
                    <th class="px-4 py-2 border text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($destinations as $destination)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2 border">{{ $destination->name }}</td>
                    <td class="px-4 py-2 border">
                        <form action="{{ route('repair-destinations.destroy', $destination->id) }}" method="POST" onsubmit="return confirm('Delete this repair destination?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
