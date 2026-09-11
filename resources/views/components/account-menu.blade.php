@props(['variant' => 'header'])

@auth
    <div class="relative {{ $variant === 'header' ? '' : 'w-full' }}" x-data="{ accountMenuOpen: false }" @click.outside="accountMenuOpen = false" @keydown.escape.window="accountMenuOpen = false">
        <button type="button" @click="accountMenuOpen = ! accountMenuOpen" :aria-expanded="accountMenuOpen" aria-haspopup="menu" class="flex items-center rounded-2xl text-left transition focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 {{ $variant === 'header' ? 'gap-2 p-1.5 hover:bg-[#fbf8f2]' : 'w-full gap-3 border border-stone-200 bg-white/60 p-3 hover:bg-white' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            <span class="min-w-0 {{ $variant === 'header' ? 'hidden text-right sm:block' : 'flex-1' }}"><span class="block truncate text-sm font-semibold text-stone-800">{{ auth()->user()->name }}</span><span class="block truncate text-xs text-stone-500">{{ auth()->user()->email }}</span></span>
            <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 text-stone-400 transition" :class="accountMenuOpen && 'rotate-180'" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" /></svg>
        </button>
        <div x-cloak x-show="accountMenuOpen" x-transition.origin.top.right role="menu" class="absolute z-[60] w-72 overflow-hidden rounded-2xl border border-stone-200 bg-[#fbf8f2] shadow-xl {{ $variant === 'header' ? 'right-0 mt-2' : 'bottom-full left-0 mb-2 max-w-full' }}">
            <div class="border-b border-stone-200 px-4 py-3"><p class="truncate text-sm font-semibold text-stone-900">{{ auth()->user()->name }}</p><p class="truncate text-xs text-stone-500">{{ auth()->user()->email }}</p></div>
            <a href="{{ route('settings') }}" role="menuitem" @click="accountMenuOpen = false; sidebarOpen = false" class="block px-4 py-3 text-sm font-medium text-stone-700 transition hover:bg-stone-100 focus:bg-stone-100 focus:outline-none">Settings</a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-stone-200">@csrf<button type="submit" role="menuitem" class="w-full px-4 py-3 text-left text-sm font-medium text-red-700 transition hover:bg-red-50 focus:bg-red-50 focus:outline-none">Log out</button></form>
        </div>
    </div>
@endauth
