<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Setting.php';

class SettingController extends Controller {
    public function index() {
        $this->authorize('manage_users'); // Only owner can access settings
        $user = auth();
        
        if ($user->role !== 'owner') {
            $this->abort(403);
        }
        
        $settings = Setting::getAll();
        $groups = [
            'general' => 'Umum',
            'billing' => 'Penagihan',
            'notification' => 'Notifikasi',
        ];
        
        $this->view('settings.index', [
            'settings' => $settings,
            'groups' => $groups,
        ]);
    }

    public function update() {
        $this->authorize('manage_users');
        $user = auth();
        
        if ($user->role !== 'owner') {
            $this->abort(403);
        }
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $db = db();
        $existing = [];
        $allSettings = $db->table('settings')->get();
        foreach ($allSettings as $row) {
            $existing[$row['key']] = $row;
        }

        // Boolean keys that must be written even when unchecked
        $boolKeys = ['notification_email_enabled', 'notification_whatsapp_enabled'];
        foreach ($boolKeys as $boolKey) {
            if (!array_key_exists($boolKey, $existing)) {
                continue;
            }
            $value = isset($_POST[$boolKey]) ? 'true' : 'false';
            $db->table('settings')->where('key', $boolKey)->update(['value' => $value]);
        }

        // Update settings from POST
        foreach ($_POST as $key => $value) {
            if ($key === '_token') continue;
            if (in_array($key, $boolKeys, true)) continue;
            if (!array_key_exists($key, $existing)) continue;
            $db->table('settings')->where('key', $key)->update(['value' => $value]);
        }
        
        log_activity('settings_update', 'Settings updated', 'setting');
        $this->flash('success', 'Pengaturan berhasil diperbarui');
        return $this->back();
    }
}