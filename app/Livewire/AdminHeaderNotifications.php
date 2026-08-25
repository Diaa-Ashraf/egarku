<?php

namespace App\Livewire;

use App\Models\ContactLog;
use App\Models\Notification;
use App\Models\Transaction;
use Livewire\Component;

class AdminHeaderNotifications extends Component
{
    public function render()
    {
        $unreadCount = Notification::where('is_read', false)->count();
        $pendingPayments = Transaction::where('status', 'pending')->count();
        $recentContacts = ContactLog::where('created_at', '>=', now()->subDay())->count();

        $totalBadge = $unreadCount + $pendingPayments + $recentContacts;

        $latestNotifications = Notification::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.admin-header-notifications', [
            'totalBadge'          => $totalBadge,
            'unreadCount'         => $unreadCount,
            'pendingPayments'     => $pendingPayments,
            'recentContacts'      => $recentContacts,
            'latestNotifications' => $latestNotifications,
        ]);
    }
}
