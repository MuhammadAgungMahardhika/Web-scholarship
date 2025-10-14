<x-guest-layout>
    <style>
        html,
        body {
            overflow: hidden;
            height: 100%;
        }

        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>

    <!-- Logo bagian atas -->
    <div class="absolute top-0 left-0 z-10" style="padding: 8px; margin:20px">
        <div class="absolute inset-0 rounded"></div>
        <img src="{{ asset(Config('global.logo')) }}" alt="{{ config('app.name') }} Logo" width="100" class="relative">
    </div>

    <!-- Background Image -->
    <img src="{{ asset('images/education.svg') }}" alt="Background"
        class="fixed top-0 left-0 w-full max-h-[90vh] object-cover opacity-80 z-[-1]">

    <!-- Container -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="w-full max-w-5xl rounded-xl p-8 relative z-10">
            <div class="w-full lg:w-1/2 mx-auto">
                <div class="flex flex-col sm:justify-center items-center">

                    <!-- Card Register -->
                    <div class="w-full sm:max-w-md px-6 py-4 shadow-md rounded-lg bg-overlay backdrop-blur-lg">

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
