<x-guest-layout>
    <div class="flex flex-row justify-center space-x-4 uppercase text-white text-sm">
        <a href="{{ route('login') }}">{{ __('Login') }}</a>
        <a href="{{ route('register') }}">{{ __('Register') }}</a>
    </div>
</x-guest-layout>
