<?php

namespace App\Livewire;

use App\Models\Ad;
use App\Models\ContactLog;
use App\Models\Notification;
use App\Models\Transaction;
use Livewire\Component;

class AdminHeaderNotifications extends Component
{
    public function markAllAsRead(): void
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function openNotification(int $notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return redirect()->to($notification->target_url);
        }

        return redirect()->to('/admin/notifications');
    }

    public function render()
    {
        $unreadCount = Notification::where('is_read', false)->count();
        $pendingAds = Ad::where('status', 'pending')->count();
        $pendingPayments = Transaction::where('status', 'pending')->count();
        $recentContacts = ContactLog::where('created_at', '>=', now()->subDay())->count();

        $totalBadge = $unreadCount + $pendingAds + $pendingPayments + $recentContacts;

        $latestNotifications = Notification::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('livewire.admin-header-notifications', [
            'totalBadge'          => $totalBadge,
            'unreadCount'         => $unreadCount,
            'pendingAds'          => $pendingAds,
            'pendingPayments'     => $pendingPayments,
            'recentContacts'      => $recentContacts,
            'latestNotifications' => $latestNotifications,
        ]);
    }
}
