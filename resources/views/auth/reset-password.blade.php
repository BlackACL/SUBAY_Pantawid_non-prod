<x-guest-layout>
        <div class="w-full max-w-md bg-white rounded-xl p-6">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <!-- Password Requirements -->
                    <div class="mt-2 text-sm text-gray-600">
                        <p class="font-medium mb-1">Password Requirements:</p>
                        <ul class="space-y-1 text-xs">
                            <li id="length-check" class="flex items-center">
                                <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                                8-15 characters long
                            </li>
                            <li id="uppercase-check" class="flex items-center">
                                <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                                At least one uppercase letter (A-Z)
                            </li>
                            <li id="lowercase-check" class="flex items-center">
                                <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                                At least one lowercase letter (a-z)
                            </li>
                            <li id="number-check" class="flex items-center">
                                <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                                At least one number (0-9)
                            </li>
                            <li id="special-check" class="flex items-center">
                                <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                                At least one special character (!@#$%^&* etc.)
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <x-primary-button class="w-full justify-center">
                        {{ __('Reset Password') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
   

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Check length (8-15 characters)
                const lengthCheck = document.getElementById('length-check');
                const lengthSpan = lengthCheck.querySelector('span');
                if (password.length >= 8 && password.length <= 15) {
                    lengthSpan.textContent = '✓';
                    lengthSpan.className = 'w-4 h-4 mr-2 text-green-500';
                    lengthCheck.className = 'flex items-center text-green-600';
                } else {
                    lengthSpan.textContent = '○';
                    lengthSpan.className = 'w-4 h-4 mr-2 text-gray-400';
                    lengthCheck.className = 'flex items-center text-gray-600';
                }
                
                // Check uppercase
                const uppercaseCheck = document.getElementById('uppercase-check');
                const uppercaseSpan = uppercaseCheck.querySelector('span');
                if (/[A-Z]/.test(password)) {
                    uppercaseSpan.textContent = '✓';
                    uppercaseSpan.className = 'w-4 h-4 mr-2 text-green-500';
                    uppercaseCheck.className = 'flex items-center text-green-600';
                } else {
                    uppercaseSpan.textContent = '○';
                    uppercaseSpan.className = 'w-4 h-4 mr-2 text-gray-400';
                    uppercaseCheck.className = 'flex items-center text-gray-600';
                }
                
                // Check lowercase
                const lowercaseCheck = document.getElementById('lowercase-check');
                const lowercaseSpan = lowercaseCheck.querySelector('span');
                if (/[a-z]/.test(password)) {
                    lowercaseSpan.textContent = '✓';
                    lowercaseSpan.className = 'w-4 h-4 mr-2 text-green-500';
                    lowercaseCheck.className = 'flex items-center text-green-600';
                } else {
                    lowercaseSpan.textContent = '○';
                    lowercaseSpan.className = 'w-4 h-4 mr-2 text-gray-400';
                    lowercaseCheck.className = 'flex items-center text-gray-600';
                }
                
                // Check numbers
                const numberCheck = document.getElementById('number-check');
                const numberSpan = numberCheck.querySelector('span');
                if (/\d/.test(password)) {
                    numberSpan.textContent = '✓';
                    numberSpan.className = 'w-4 h-4 mr-2 text-green-500';
                    numberCheck.className = 'flex items-center text-green-600';
                } else {
                    numberSpan.textContent = '○';
                    numberSpan.className = 'w-4 h-4 mr-2 text-gray-400';
                    numberCheck.className = 'flex items-center text-gray-600';
                }
                
                // Check special characters
                const specialCheck = document.getElementById('special-check');
                const specialSpan = specialCheck.querySelector('span');
                if (/[^A-Za-z0-9]/.test(password)) {
                    specialSpan.textContent = '✓';
                    specialSpan.className = 'w-4 h-4 mr-2 text-green-500';
                    specialCheck.className = 'flex items-center text-green-600';
                } else {
                    specialSpan.textContent = '○';
                    specialSpan.className = 'w-4 h-4 mr-2 text-gray-400';
                    specialCheck.className = 'flex items-center text-gray-600';
                }
            });
        });
    </script>
</x-guest-layout>
