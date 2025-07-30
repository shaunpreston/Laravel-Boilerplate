@if(request()->user()->two_factor_confirmed_at)
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('2FA Recovery Codes') }}
            </h2>
        </header>

        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400" x-data="{ show: false }">
            <x-button x-on:click.prevent="show = !show">
                {{ __('Toggle Recovery Codes') }}
            </x-button>

            <div class="mt-4" x-show="show">
                @foreach(request()->user()->recoveryCodes() as $code)
                    {{ $code }}
                @endforeach
            </div>
        </div>
    </section>
@endif
