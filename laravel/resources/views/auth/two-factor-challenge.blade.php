<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Confirm 2FA') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Add one time code to continue.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('two-factor.login.store') }}" class="mt-6 space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="code" :value="__('Code')" />
                                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" autocomplete="code" />
                                <x-input-error :messages="$errors->updatePassword->get('code')" class="mt-2" />
                            </div>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('If you do not have access to your authenticator app, please use one of your account recovery codes below.') }}
                            </p>

                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400" x-data="{ show: false }">
                                <x-button x-on:click.prevent="show = !show">
                                    {{ __('Recovery Code') }}
                                </x-button>

                                <div class="mt-4" x-show="show">
                                    <x-input-label for="recovery_code" :value="__('Recovery Code')" />
                                    <x-text-input id="recovery_code" name="recovery_code" type="text" class="mt-1 block w-full" autocomplete="recovery_code" />
                                    <x-input-error :messages="$errors->updatePassword->get('recovery_code')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-button>{{ __('Confirm') }}</x-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

