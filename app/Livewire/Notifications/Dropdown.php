<?php

namespace App\Livewire\Notifications;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dropdown extends Component
{
    public function markAsRead(string $notificationId): void
    {
        $notification = $this->authenticatedUser()->notifications()->whereKey($notificationId)->first();

        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        $this->authenticatedUser()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function clear(string $notificationId): void
    {
        $this->authenticatedUser()->notifications()->whereKey($notificationId)->delete();
    }

    public function clearAll(): void
    {
        $this->authenticatedUser()->notifications()->delete();
        $this->dispatch('notifications-cleared');
    }

    public function render(): View
    {
        $user = $this->authenticatedUser();

        return view('livewire.notifications.dropdown', [
            'notifications' => $user->notifications()->latest()->limit(10)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    private function authenticatedUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
