<div class="relative" x-data="{ open: false, confirmingClearAll: false }" @click.outside="open = false" @keydown.escape.window="open = false; confirmingClearAll = false">
    <button type="button" @click="open = ! open" class="relative flex h-10 w-10 items-center justify-center rounded-xl text-stone-600 transition hover:bg-[#fbf8f2] hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" aria-label="Notifications" aria-haspopup="true" :aria-expanded="open">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path stroke-linecap="round" d="M10 21h4"/></svg>
        @if ($unreadCount > 0)<span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-emerald-700 px-1.5 py-0.5 text-center text-[10px] font-bold text-white ring-2 ring-[#f5f2eb]" aria-label="{{ $unreadCount }} unread notifications">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
    </button>

    <div x-cloak x-show="open" x-transition.origin.top.right class="fixed inset-x-3 top-16 z-50 max-h-[75vh] overflow-hidden rounded-2xl border border-stone-200 bg-[#fbf8f2] shadow-xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-96" role="region" aria-label="Notification history">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-stone-600">Notifications</p>
            @if ($unreadCount > 0)<button type="button" wire:click="markAllAsRead" wire:loading.attr="disabled" wire:target="markAllAsRead" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 disabled:opacity-50">Mark all as read</button>@endif
        </div>

        <div class="max-h-[58vh] overflow-y-auto">
            @forelse ($notifications as $notification)
                <article wire:key="notification-{{ $notification->id }}" class="relative border-b border-stone-100 px-4 py-3 last:border-0 {{ $notification->read_at ? '' : 'bg-emerald-50/50' }}" x-data="{ actionsOpen: false }" @click.outside="actionsOpen = false">
                    <div class="flex items-start gap-3">
                        @unless ($notification->read_at)<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-600" aria-label="Unread"></span>@endunless
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-stone-800">{{ data_get($notification->data, 'notification_title', 'Spendly notification') }}</p>
                            <p class="mt-0.5 truncate text-xs font-medium text-stone-700">{{ data_get($notification->data, 'title') }}</p>
                            <p class="mt-1 text-xs leading-5 text-stone-500">{{ data_get($notification->data, 'message') }}</p>
                            <time class="mt-1 block text-[10px] text-stone-400">{{ $notification->created_at->diffForHumans() }}</time>
                        </div>
                        <button type="button" @click="actionsOpen = ! actionsOpen" :aria-expanded="actionsOpen" aria-haspopup="menu" aria-label="Notification actions" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-stone-500 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-emerald-600">•••</button>
                    </div>
                    <div x-cloak x-show="actionsOpen" x-transition role="menu" class="absolute right-4 top-11 z-20 w-44 overflow-hidden rounded-xl border border-stone-200 bg-[#fbf8f2] shadow-lg">
                        @unless ($notification->read_at)<button type="button" wire:click="markAsRead('{{ $notification->id }}')" @click="actionsOpen = false" role="menuitem" class="block w-full px-3 py-2.5 text-left text-xs font-medium text-stone-700 hover:bg-stone-100">Mark as read</button>@endunless
                        <button type="button" wire:click="clear('{{ $notification->id }}')" @click="actionsOpen = false" role="menuitem" class="block w-full border-t border-stone-200 px-3 py-2.5 text-left text-xs font-medium text-red-700 hover:bg-red-50">Clear notification</button>
                    </div>
                </article>
            @empty
                <div class="px-6 py-9 text-center"><p class="text-sm font-semibold text-stone-800">You're all caught up.</p><p class="mt-1 text-xs text-stone-500">New notifications will appear here.</p></div>
            @endforelse
        </div>

        @if ($notifications->isNotEmpty())<div class="border-t border-stone-200 px-4 py-3 text-right"><button type="button" @click="confirmingClearAll = true" class="text-xs font-semibold text-red-700 hover:text-red-800">Clear all</button></div>@endif
    </div>

    <div x-cloak x-show="confirmingClearAll" class="fixed inset-0 z-[70] flex items-center justify-center bg-stone-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="clear-notifications-title">
        <div @click.outside="confirmingClearAll = false" class="w-full max-w-md rounded-2xl border border-stone-200 bg-[#fbf8f2] p-5 shadow-xl sm:p-6">
            <h2 id="clear-notifications-title" class="text-lg font-semibold text-stone-900">Clear all notifications?</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">This removes your notification history. It does not affect your transactions or recurring schedules.</p>
            <div class="mt-6 flex justify-end gap-3"><button type="button" @click="confirmingClearAll = false" class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700">Cancel</button><button type="button" wire:click="clearAll" @click="confirmingClearAll = false" wire:loading.attr="disabled" wire:target="clearAll" class="rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-50">Clear all</button></div>
        </div>
    </div>
</div>
