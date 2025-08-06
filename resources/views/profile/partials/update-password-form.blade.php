<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Your new password must be 8-15 characters, and include uppercase, lowercase, a number, and a symbol.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            
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

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('update_password_password');
            
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
</section>
