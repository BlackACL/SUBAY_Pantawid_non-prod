<x-guest-layout>
    <div class="w-full max-w-md mx-auto bg-white shadow-md rounded-lg p-6 mt-10">

        @if ($errors->any())
            <div class="mb-4 text-red-600 text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 text-green-600 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <h2 class="text-lg font-medium text-gray-900 mb-4 text-center">
            Two-Factor Authentication
        </h2>

        <form method="POST" action="{{ route('verify.process') }}">
            @csrf

            <div class="mb-4">
                <label for="two_factor_code" class="block text-sm font-medium text-gray-700">
                    Enter the 6-digit code
                </label>
                <input type="text" name="two_factor_code" id="two_factor_code" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm 
                           focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex justify-center">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Verify Code
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600 mb-2">
                Didn't receive the code or it expired?
            </p>
            <form method="POST" action="{{ route('verify.resend') }}" class="inline">
                @csrf
                <button type="submit" 
                    class="text-blue-600 hover:text-blue-800 underline text-sm">
                    Resend Code
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
