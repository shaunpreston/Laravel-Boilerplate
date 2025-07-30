@if (! request()->user()->hasEnabledTwoFactorAuthentication() && session('status') != 'two-factor-authentication-enabled')
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Enable 2FA') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Activate 2FA below") }}
            </p>
        </header>

        <form id="enable-2fa" method="post" action="{{ route('two-factor.enable') }}" class="mt-6 space-y-6">
            @csrf

            <x-button>{{ __('Enable') }}</x-button>
        </form>
    </section>
@endif
