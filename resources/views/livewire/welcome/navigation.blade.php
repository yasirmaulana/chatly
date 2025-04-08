{{-- <nav class="-mx-3 flex flex-1 justify-end"> --}}
<div>
    @auth
        {{-- <a
            href="{{ url('/dashboard') }}"
            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
        >
            Dashboard
        </a> --}}
        <a href="{{ url('/chat') }}" class="text-sm text-gray-700 dark:text-gray-200 hover:underline">Mulai Chat</a>
    @else
        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-200 hover:underline">Log in</a> | 
        @if (Route::has('register'))
            {{-- <a
                href="{{ route('register') }}"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
                Register
            </a> --}}
            <a href="{{ route('register') }}" class="text-sm text-gray-700 dark:text-gray-200 hover:underline">Register</a>
        @endif
    @endauth
</div>
{{-- </nav> --}}
