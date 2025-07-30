@if(request()->user()->two_factor_confirmed_at)
    <section class="mt-4">
        <x-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-disable-2fa')"
        >{{ __('Disable 2FA') }}</x-button>

        <x-modal name="confirm-disable-2fa" :show="$errors->isNotEmpty()" focusable>
            <form method="post" action="{{ route('two-factor.disable') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Are you sure you want to disable 2FA?') }}
                </h2>

                <div class="mt-6 flex justify-end">
                    <x-button x-on:click.prevent="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-button>

                    <x-button-danger>
                        {{ __('Disable 2FA') }}
                    </x-button-danger>
                </div>
            </form>
        </x-modal>
    </section>
@endif
