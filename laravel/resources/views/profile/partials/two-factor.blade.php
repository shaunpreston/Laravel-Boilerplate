@if (session('status') == 'two-factor-authentication-enabled')
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Configure 2FA') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Please finish configuring two factor authentication below.") }}
            </p>
        </header>

        <div class="block">
            {!! request()->user()->twoFactorQrCodeSvg() !!}
        </div>

        <div class="mt-4 text-gray-900 dark:text-gray-100">
            URL: <a class="text-gray-900 dark:text-gray-100" href="{{ request()->user()->twoFactorQrCodeUrl() }}">{{ request()->user()->twoFactorQrCodeUrl() }}</a>
        </div>

        <div class="mt-4 text-gray-900 dark:text-gray-100">
            Secret: {{ decrypt(request()->user()->two_factor_secret) }}
        </div>

        <form id="confirm-2fa" method="post" action="{{ route('two-factor.confirm') }}" class="mt-6 space-y-6">
            @csrf

            <div>
                <x-input-label for="code" :value="__('Code')" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" autocomplete="code" />
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
            </div>

            <x-button>{{ __('Confirm') }}</x-button>
        </form>
    </section>
@endif
