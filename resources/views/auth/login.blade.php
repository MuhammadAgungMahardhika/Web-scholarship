<x-guest-layout>
    <style>
        /* Mencegah scrolling */
        html,
        body {
            overflow: hidden;
            height: 100%;
        }

        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
    <!-- Logo bagian atas (tetap di sudut kiri atas) -->
    <div class="absolute top-0 left-0 z-10 " style="padding: 8px; margin:20px">
        <div class="absolute inset-0  rounded"></div>
        <img src="{{ asset(Config('global.logo')) }}" alt="{{ config('app.name') }} Logo" width="100" class="relative">
    </div>
    <!-- Background Image -->
    <img src="{{ asset('images/education.svg') }}" alt="Background"
        class="fixed top-0 left-0 w-full max-h-[90vh] object-cover opacity-80 z-[-1]">



    <!-- Container untuk halaman login -->
    <div class="fixed inset-0 flex items-center justify-center">
        <!-- Wrapper untuk form login -->
        <div class="w-full max-w-5xl rounded-xl  p-8 relative z-10 ">
            <!-- Kolom Kanan: Form Login -->
            <div class="w-full lg:w-1/2 mx-auto">
                <div class="flex flex-col sm:justify-center items-center">
                    <!-- Form Login -->
                    <div class="w-full sm:max-w-md px-6 py-4 shadow-md rounded-lg">
                        <!-- Menampilkan error validation jika ada -->
                        <x-validation-errors class="mb-4" />

                        @session('status')
                            <div class="mb-4 font-medium text-sm text-green-600">
                                {{ $value }}
                            </div>
                        @endsession
                        @if (session('error'))
                            <div class="mb-4 font-medium text-sm text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            <!-- Input Email -->
                            <div>
                                <x-label for="identity" value="{{ __('Nip / Email') }}" class="text-white" />
                                <x-input id="identity"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="text" name="identity" :value="old('email')" required autofocus
                                    autocomplete="username" />
                            </div>

                            <!-- Input Password -->
                            <div class="mt-4">
                                <x-label for="password" value="{{ __('Password') }}" class="text-white" />
                                <x-input id="password"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="password" name="password" required autocomplete="current-password" />
                            </div>

                            <!-- Remember Me -->
                            <div class="block mt-4">
                                <label for="remember_me" class="flex items-center">
                                    <x-checkbox id="remember_me" name="remember" checked />
                                    <span class="ms-2 text-sm text-white">{{ __('Remember me') }}</span>
                                </label>
                            </div>

                            <!-- Lupa Password dan Tombol Login -->
                            <div class="flex items-center justify-end mt-4">
                                <x-button id="loginButton" type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-display: swap flex items-center justify-center min-w-[100px]">
                                    <span id="buttonText">{{ __('Log in') }}</span>
                                    <span id="loadingIndicator" class="hidden inline-flex items-center">
                                        <!-- Loading spinner -->
                                        <svg class="animate-spin h-5 w-5 text-white me-2"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Processing...
                                    </span>
                                </x-button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-guest-layout>
