<x-guest-layout>
    <style>
        html,
        body {
            overflow: hidden;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Background wrapper yang responsif - posisi kanan */
        .bg-wrapper {
            position: fixed;
            top: 0;
            right: 0;
            width: 55%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-wrapper img {
            max-width: 90%;
            max-height: 80vh;
            width: auto;
            height: auto;
            object-fit: contain;
            opacity: 0.8;
        }

        /* Responsif untuk tablet */
        @media (max-width: 1024px) {
            .bg-wrapper {
                width: 50%;
            }

            .bg-wrapper img {
                max-height: 60vh;
            }
        }

        /* Responsif untuk mobile - kembali ke center dengan opacity rendah */
        @media (max-width: 768px) {
            .bg-wrapper {
                width: 100%;
                left: 0;
                right: auto;
            }

            .bg-wrapper img {
                max-height: 40vh;
                max-width: 70%;
                opacity: 0.4;
            }
        }
    </style>

    <!-- Logo bagian atas -->
    <div class="absolute top-0 left-0 z-10" style="padding: 8px; margin:20px">
        <div class="absolute inset-0 rounded"></div>
        <img src="{{ asset(Config('global.logo')) }}" alt="{{ config('app.name') }} Logo" width="100" class="relative">
    </div>

    <!-- Background Image dengan wrapper responsif -->
    <div class="bg-wrapper">
        <img src="{{ asset('images/education.svg') }}" alt="Background">
    </div>

    <!-- Container -->
    <div class="fixed inset-0 flex items-center justify-start px-4 lg:px-12">
        <div class="w-full max-w-md rounded-xl p-8 relative z-10">
            <div class="w-full">
                <div class="flex flex-col justify-center items-stretch lg:items-start">

                    <!-- Card Register -->
                    <div class="w-full sm:max-w-md px-6 py-4 shadow-md rounded-lg bg-overlay ">

                        <!-- Validation Errors -->
                        <x-validation-errors class="mb-4" />

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Name -->
                            <div>
                                <x-label for="name" value="{{ __('Name') }}" class="text-dark" />
                                <x-input id="name"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="text" name="name" :value="old('name')" required autofocus
                                    autocomplete="name" />
                            </div>

                            <!-- Username -->
                            <div class="mt-4">
                                <x-label for="username" value="{{ __('Username') }}" class="text-dark" />
                                <x-input id="username"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="text" name="username" :value="old('username')" required autocomplete="username" />
                            </div>

                            <!-- Email -->
                            <div class="mt-4">
                                <x-label for="email" value="{{ __('Email') }}" class="text-dark" />
                                <x-input id="email"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="email" name="email" :value="old('email')" required autocomplete="username" />
                            </div>

                            <!-- Password -->
                            <div class="mt-4">
                                <x-label for="password" value="{{ __('Password') }}" class="text-dark" />
                                <x-input id="password"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="password" name="password" required autocomplete="new-password" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="mt-4">
                                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}"
                                    class="text-dark" />
                                <x-input id="password_confirmation"
                                    class="block mt-1 w-full border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    type="password" name="password_confirmation" required autocomplete="new-password" />
                            </div>

                            <!-- Terms (jika diaktifkan) -->
                            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                <div class="mt-4 text-white">
                                    <x-label for="terms">
                                        <div class="flex items-center">
                                            <x-checkbox name="terms" id="terms" required />
                                            <div class="ms-2">
                                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                                    'terms_of_service' =>
                                                        '<a target="_blank" href="' .
                                                        route('terms.show') .
                                                        '" class="underline text-sm text-white hover:text-gray-300">' .
                                                        __('Terms of Service') .
                                                        '</a>',
                                                    'privacy_policy' =>
                                                        '<a target="_blank" href="' .
                                                        route('policy.show') .
                                                        '" class="underline text-sm text-white hover:text-gray-300">' .
                                                        __('Privacy Policy') .
                                                        '</a>',
                                                ]) !!}
                                            </div>
                                        </div>
                                    </x-label>
                                </div>
                            @endif

                            <!-- Tombol Register -->
                            <div class="flex items-center justify-between mt-6">
                                <a class="underline text-sm text-dark hover:text-gray-300" href="{{ route('login') }}">
                                    {{ __('Already registered?') }}
                                </a>

                                <x-button
                                    class="bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-display: swap flex items-center justify-center min-w-[100px]">
                                    <span>{{ __('Register') }}</span>
                                </x-button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
