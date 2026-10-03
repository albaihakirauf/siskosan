<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Notification.php';

class NotificationController extends Controller {
    public function index() {
        $user = auth();
        $page = (int)($_GET['page'] ?? 1);
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);
        
        $unreadCount = Notification::getUnreadCount($user->id);
        
        $this->view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead($id) {
        $user = auth();
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $notification = Notification::find($id);
        if (!$notification || $notification->user_id !== $user->id) {
            $this->flash('error', 'Notifikasi tidak ditemukan');
            return $this->back();
        }

        $notification->markAsRead();
        
        if ($this->wantsJson()) {
            return $this->json(['success' => true]);
        }
        return $this->back();
    }

    public function markAllRead() {
        $user = auth();
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            if ($this->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Token CSRF tidak valid']);
            }
            return $this->back();
        }

        Notification::markAllAsRead($user->id);
        
        if ($this->wantsJson()) {
            return $this->json(['success' => true, 'message' => 'Semua notifikasi ditandai dibaca']);
        }
        $this->flash('success', 'Semua notifikasi ditandai dibaca');
        return $this->back();
    }

    private function wantsJson() {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    public function getUnreadCount() {
        $user = auth();
        $count = Notification::getUnreadCount($user->id);
        
        return $this->json(['count' => $count]);
    }

    public function getRecent() {
        $user = auth();
        $limit = (int)($_GET['limit'] ?? 10);
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        
        return $this->json(['notifications' => $notifications]);
    }
}