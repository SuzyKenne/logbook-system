<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Logbook App') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dark-bg': '#0a0a0a',
                        'dark-card': '#1a1a1a',
                        'dark-input': '#2a2a2a',
                        'dark-border': '#3a3a3a',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-bg min-h-screen flex items-center justify-center p-5">
    <!-- Success Message -->
    @if (session('success'))
    <div class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
    <div class="fixed top-4 right-4 bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
    @endif

    <!-- Status Message -->
    @if (session('status'))
    <div class="fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        {{ session('status') }}
    </div>
    @endif

    <div class="w-full max-w-md">
        <div class="bg-dark-card rounded-xl p-10 shadow-2xl border border-dark-border">
            <!-- App Title -->
            <div class="text-center mb-2">
                <h1 class="text-white text-lg font-semibold tracking-wide">LOGBOOK_APP</h1>
            </div>

            <!-- Sign In Title -->
            <div class="text-center mb-8">
                <h2 class="text-white text-3xl font-normal">Sign in</h2>
            </div>

            <!-- Login Form -->
            <form class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-2">
                        Email address<span class="text-red-400 ml-0.5">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="w-full px-4 py-3 bg-dark-input border border-dark-border rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                        required
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-white text-sm font-medium mb-2">
                        Password<span class="text-red-400 ml-0.5">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            class="w-full px-4 py-3 bg-dark-input border border-dark-border rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 pr-12"
                            required
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors duration-200"
                            onclick="togglePassword()"
                        >
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="w-4 h-4 text-orange-500 bg-dark-input border border-dark-border rounded focus:ring-orange-500 focus:ring-2"
                    >
                    <label for="remember" class="ml-3 text-white text-sm">
                        Remember me
                    </label>
                </div>

                <!-- Sign In Button -->
                <button 
                    type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-dark-card"
                >
                    Sign in
                </button>
            </form>

            <!-- Additional Links (Optional) -->
            <div class="mt-6 text-center">
                <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-200">
                    Forgot your password?
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l3.121-3.121M12 12v.01"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.fixed.top-4.right-4');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transform = 'translateX(100%)';
                setTimeout(() => message.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>