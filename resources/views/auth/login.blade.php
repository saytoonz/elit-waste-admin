<x-guest-layout>
    <div class="flex min-h-screen">
        <!-- Left Side: Branding & Illustration -->
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-primary to-secondary relative flex-col justify-between p-12 text-white overflow-hidden">
            <div class="z-10">
                 <!-- Logo -->
                 <div class="flex items-center gap-3">
                    @if(!empty($globalCompanyLogo))
                        <img src="{{ asset($globalCompanyLogo) }}" alt="{{ $globalCompanyName }}" class="h-16 w-auto rounded-lg shadow-lg bg-white p-1">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-primary text-xl font-bold shadow-lg">
                            {{ substr($globalCompanyName ?? 'EW', 0, 2) }}
                        </div>
                    @endif
                    <span class="text-2xl font-bold tracking-wide">{{ $globalCompanyName ?? 'Elite Waste Management' }}</span>
                 </div>
            </div>

            <div class="z-10 relative">
                <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                    Healthy environment,<br>
                    <span class="text-accent-cyan">a key to long life.</span>
                </h1>
                <p class="text-lg text-light max-w-md">
                    Manage customers, billing, and payments efficiently using the {{ $globalCompanyName ?? 'Elite Waste' }} Dashboard.
                </p>
            </div>

            <div class="z-10 text-sm text-light opacity-80">
                &copy; {{ date('Y') }} {{ $globalCompanyName ?? 'Elite Waste Management' }}. All rights reserved.
            </div>

            <!-- Decorative Circles -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-accent-orange opacity-20 rounded-full blur-3xl"></div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-background">
            <div class="w-full max-w-md space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
                
                <!-- Mobile Branding -->
                <div class="lg:hidden text-center mb-8">
                    @if(!empty($globalCompanyLogo))
                        <img src="{{ asset($globalCompanyLogo) }}" alt="{{ $globalCompanyName }}" class="mx-auto h-20 w-auto rounded-lg shadow-lg bg-white p-1 mb-4">
                    @else
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary text-white text-2xl font-bold shadow-lg ring-4 ring-light mb-4">
                             {{ substr($globalCompanyName ?? 'EW', 0, 2) }}
                        </div>
                    @endif
                    <h2 class="text-2xl font-bold text-gray-900">{{ $globalCompanyName ?? 'Elite Waste Admin' }}</h2>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">Welcome back</h2>
                    <p class="mt-2 text-sm text-gray-600">Please enter your details to sign in.</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" autocomplete="email" required autofocus 
                                    class="block w-full rounded-lg border-gray-300 py-3 text-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-gray-50"
                                    placeholder="admin@elitwaste.com"
                                    value="{{ old('email') }}">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                @if (Route::has('password.request'))
                                    <div class="text-sm">
                                        <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-secondary">Forgot password?</a>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                    class="block w-full rounded-lg border-gray-300 py-3 text-gray-900 shadow-sm focus:border-primary focus:ring-primary sm:text-sm bg-gray-50"
                                    placeholder="••••••••">
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                         <div class="flex items-center">
                            <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-900">Remember for 30 days</label>
                        </div>
                    </div>

                    <button type="submit" 
                        class="flex w-full justify-center rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white shadow-lg shadow-purple-200 hover:bg-purple-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform hover:-translate-y-0.5">
                        Sign in
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
