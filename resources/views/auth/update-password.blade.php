<x-superadmin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Update Password') }}
        </h2>
    </x-slot>

    <div class="py-4 px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg px-10 py-5 w-full">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded relative" id="success-alert">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-700 font-medium flex-1">{{ session('success') }}</p>
                        <button onclick="closeAlert('success-alert')" 
                            class="ml-auto text-green-500 hover:text-green-700 focus:outline-none transition-colors duration-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded relative" id="error-alert">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 font-medium flex-1">{{ session('error') }}</p>
                        <button onclick="closeAlert('error-alert')" 
                            class="ml-auto text-red-500 hover:text-red-700 focus:outline-none transition-colors duration-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <header>
                <p class="text-base text-gray-700">
                    {{ __('Your new password must be 8-15 characters, and include uppercase, lowercase, a number, and a symbol.') }}
                </p>
            </header>

            <form id="update-password-form" method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5 text-lg">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="update_password_current_password" class="text-lg font-medium" :value="__('Current Password')" />
                    <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full text-sm p-2 password-field" autocomplete="current-password" />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-600" />
                </div>

                <div>
                    <x-input-label for="update_password_password" class="text-lg font-medium" :value="__('New Password')" />
                    <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full text-sm p-2 password-field" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-600" />

                    <!-- Password Requirements -->
                    <div class="mt-3 text-sm text-gray-700">
                        <p class="font-semibold mb-2">Password Requirements:</p>
                        <ul class="space-y-2 text-sm">
                            <li id="length-check" class="flex items-center">
                                <span class="w-5 h-5 mr-2 text-gray-400">○</span>
                                8-15 characters long
                            </li>
                            <li id="uppercase-check" class="flex items-center">
                                <span class="w-5 h-5 mr-2 text-gray-400">○</span>
                                At least one uppercase letter (A-Z)
                            </li>
                            <li id="lowercase-check" class="flex items-center">
                                <span class="w-5 h-5 mr-2 text-gray-400">○</span>
                                At least one lowercase letter (a-z)
                            </li>
                            <li id="number-check" class="flex items-center">
                                <span class="w-5 h-5 mr-2 text-gray-400">○</span>
                                At least one number (0-9)
                            </li>
                            <li id="special-check" class="flex items-center">
                                <span class="w-5 h-5 mr-2 text-gray-400">○</span>
                                At least one special character (!@#$%^&* etc.)
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <x-input-label for="update_password_password_confirmation" class="text-lg font-medium" :value="__('Confirm Password')" />
                    <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full text-sm p-2 password-field" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-600" />
                </div>

                <!-- Show Password Toggle -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="togglePassword" class="h-5 w-5">
                    <label for="togglePassword" class="text-sm text-gray-700 cursor-pointer">Show Passwords</label>
                </div>

                <div class="flex items-center gap-6">
                    <x-primary-button id="save-btn" disabled class="px-6 py-3 text-sm opacity-50 cursor-not-allowed">
                        {{ __('Save') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>

    {{-- Script for validation + show password --}}
    <script>
        // Function to close alert messages
        function closeAlert(alertId) {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                alertElement.style.opacity = '0';
                alertElement.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    alertElement.remove();
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('update_password_password');
            const confirmInput = document.getElementById('update_password_password_confirmation');
            const currentInput = document.getElementById('update_password_current_password');
            const saveBtn = document.getElementById('save-btn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordFields = document.querySelectorAll('.password-field');

            function validatePasswordRequirements() {
                const password = passwordInput.value;
                const currentPassword = currentInput.value;
                let passwordRequirementsMet = true;

                function check(condition, elementId) {
                    const el = document.getElementById(elementId);
                    const span = el.querySelector('span');
                    if (condition) {
                        span.textContent = '✓';
                        span.className = 'w-5 h-5 mr-2 text-green-500';
                        el.className = 'flex items-center text-green-600';
                    } else {
                        span.textContent = '○';
                        span.className = 'w-5 h-5 mr-2 text-gray-400';
                        el.className = 'flex items-center text-gray-600';
                        passwordRequirementsMet = false;
                    }
                }

                check(password.length >= 8 && password.length <= 15, 'length-check');
                check(/[A-Z]/.test(password), 'uppercase-check');
                check(/[a-z]/.test(password), 'lowercase-check');
                check(/\d/.test(password), 'number-check');
                check(/[^A-Za-z0-9]/.test(password), 'special-check');

                // Enable save button if current password is filled and new password meets requirements
                const shouldEnableButton = currentPassword.trim() !== '' && passwordRequirementsMet && password.trim() !== '';
                
                saveBtn.disabled = !shouldEnableButton;
                saveBtn.className = shouldEnableButton
                    ? 'px-6 py-3 text-lg bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition'
                    : 'px-6 py-3 text-lg bg-indigo-600 text-white rounded-md opacity-50 cursor-not-allowed';
            }

                // validate inputs (but don't check password confirmation match here)
                passwordInput.addEventListener('input', validatePasswordRequirements);
                confirmInput.addEventListener('input', validatePasswordRequirements);
                currentInput.addEventListener('input', validatePasswordRequirements);            // toggle password visibility
            togglePassword.addEventListener('change', function() {
                passwordFields.forEach(field => {
                    field.type = this.checked ? 'text' : 'password';
                });
            });
        });
    </script>
</x-superadmin-layout>
