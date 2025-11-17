<x-guest-layout>
    <div class="w-full max-w-md bg-white rounded-xl p-6">
        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative">
                    <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required autocomplete="new-password" />
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div id="strengthBar" class="h-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <span id="strengthText" class="text-xs font-medium text-gray-500">Strength</span>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    <p class="font-medium mb-2">Password Requirements:</p>
                    <ul class="space-y-1 text-xs">
                        <li id="length-check" class="flex items-center">
                            <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                            At least 12 characters long
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
                        <li id="common-check" class="flex items-center">
                            <span class="w-4 h-4 mr-2 text-gray-400">○</span>
                            Not a common password
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <div class="relative">
                    <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
            <div class="mt-4 text-sm">
                <p class="text-gray-600">
                    By resetting your password, you agree to our 
                    <a href="{{ asset('storage/DPA-of-2012_1.pdf') }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">
                        Data Privacy Policy
                    </a>
                </p>
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
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            // Common passwords list (same as backend validation)
            const commonPasswords = [
                'password', 'password123', '12345678', 'qwerty', 'abc123',
                'monkey', '1234567', 'letmein', 'trustno1', 'dragon',
                'baseball', '111111', 'iloveyou', 'master', 'sunshine',
                'ashley', 'bailey', 'shadow', '123123', '654321',
                'superman', 'qazwsx', 'michael', 'football', 'admin',
                'welcome', 'login', 'passw0rd', 'admin123', 'root',
                'pantawid123', 'dswd123', 'dswd2024', 'admin2024'
            ];

            // Password requirements checks
            const checks = {
                length: { element: document.getElementById('length-check'), test: password => password.length >= 12 },
                uppercase: { element: document.getElementById('uppercase-check'), test: password => /[A-Z]/.test(password) },
                lowercase: { element: document.getElementById('lowercase-check'), test: password => /[a-z]/.test(password) },
                number: { element: document.getElementById('number-check'), test: password => /[0-9]/.test(password) },
                special: { element: document.getElementById('special-check'), test: password => /[!@#$%^&*(),.?":{}|<>]/.test(password) },
                common: { element: document.getElementById('common-check'), test: password => !commonPasswords.includes(password.toLowerCase()) }
            };

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
            });

            // Calculate password strength
            function calculateStrength(password) {
                let strength = 0;
                
                if (password.length >= 12) strength += 20;
                if (password.length >= 16) strength += 10;
                if (/[a-z]/.test(password)) strength += 15;
                if (/[A-Z]/.test(password)) strength += 15;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 15;
                if (password.length >= 20) strength += 10;
                
                // Penalty for common passwords
                if (commonPasswords.includes(password.toLowerCase())) {
                    strength = Math.max(0, strength - 50);
                }
                
                return Math.min(100, strength);
            }

            // Update strength meter
            function updateStrengthMeter(strength) {
                strengthBar.style.width = strength + '%';
                
                if (strength <= 20) {
                    strengthBar.style.backgroundColor = '#ef4444'; // red
                    strengthText.textContent = 'Very Weak';
                    strengthText.className = 'text-xs font-medium text-red-600';
                } else if (strength <= 40) {
                    strengthBar.style.backgroundColor = '#f97316'; // orange
                    strengthText.textContent = 'Weak';
                    strengthText.className = 'text-xs font-medium text-orange-600';
                } else if (strength <= 60) {
                    strengthBar.style.backgroundColor = '#eab308'; // yellow
                    strengthText.textContent = 'Fair';
                    strengthText.className = 'text-xs font-medium text-yellow-600';
                } else if (strength <= 80) {
                    strengthBar.style.backgroundColor = '#3b82f6'; // blue
                    strengthText.textContent = 'Good';
                    strengthText.className = 'text-xs font-medium text-blue-600';
                } else {
                    strengthBar.style.backgroundColor = '#22c55e'; // green
                    strengthText.textContent = 'Strong';
                    strengthText.className = 'text-xs font-medium text-green-600';
                }
            }

            // Real-time password validation
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Update requirement checks
                Object.values(checks).forEach(check => {
                    const passed = check.test(password);
                    const icon = check.element.querySelector('span');
                    
                    if (passed) {
                        icon.textContent = '✓';
                        icon.classList.remove('text-gray-400');
                        icon.classList.add('text-green-600');
                        check.element.classList.remove('text-gray-600');
                        check.element.classList.add('text-green-600');
                    } else {
                        icon.textContent = '○';
                        icon.classList.remove('text-green-600');
                        icon.classList.add('text-gray-400');
                        check.element.classList.remove('text-green-600');
                        check.element.classList.add('text-gray-600');
                    }
                });

                // Update strength meter
                const strength = calculateStrength(password);
                updateStrengthMeter(strength);
            });
        });
    </script>
</x-guest-layout>
