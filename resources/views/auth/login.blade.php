<x-guest-layout>
    <!-- Login Title -->
    <div class="flex justify-center mb-4">
        <img src="{{ asset('images/subay_black.png') }}" 
            alt="Black Logo" 
            class="max-h-28 sm:max-h-32 md:max-h-36 lg:max-h-40 w-auto">
    </div>
    
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-6 px-6 pb-5">
        @csrf
        <!-- Email -->
        <div>
            <label for="email" class="block text-md font-medium text-gray-700">Email</label>
            <input id="email" name="email" type="email" required autofocus
                class="bg-[#cbcbe4] mt-2 block w-full rounded border border-black px-3 py-2 mb-5 focus:border-black sm:text-sm text-[#002451]">
            @if ($errors->has('email'))
                <p class="text-red-600 text-xs mt-1">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-md font-medium text-gray-700">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" required
                    class="bg-[#cbcbe4] mt-2 block w-full rounded border border-black px-3 py-2 focus:border-black sm:text-sm pr-10 text-[#002451]">
                <button type="button" onclick="togglePasswordVisibility()" tabindex="-1" 
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 focus:outline-none">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @if ($errors->has('password'))
                <p class="text-red-600 text-xs mt-1">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <!-- Submit -->
        <div class="flex flex-col items-center mt-6">
            <button type="submit" class="bg-[#000033] text-lg text-white px-10 py-3 rounded my-6">
                Log In
            </button>
        </div>
    </form>
</x-guest-layout>

<!-- Password Toggle Script -->
<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95m3.25-2.69A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M3 3l18 18" />
        `;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}
</script>
