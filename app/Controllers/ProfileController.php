<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Property.php';

class ProfileController extends Controller {
    public function index() {
        $user = auth();
        $this->view('profile.index', ['user' => $user]);
    }

    public function edit() {
        $user = auth();
        $this->view('profile.edit', ['user' => $user]);
    }

    public function update() {
        $user = auth();
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'phone' => 'max:20',
            'current_password' => 'max:100',
            'password' => 'min:6|confirmed',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $db = db();
        
        // Check email uniqueness
        if ($_POST['email'] !== $user->email) {
            $exists = $db->table('users')->where('email', $_POST['email'])->where('id', '!=', $user->id)->exists();
            if ($exists) {
                $this->flash('error', 'Email sudah digunakan');
                return $this->back();
            }
        }

        $data = [
            'name' => trim($_POST['name']),
            'email' => strtolower(trim($_POST['email'])),
            'phone' => $_POST['phone'] ?? null,
        ];

        // Handle password change
        if (!empty($_POST['current_password'])) {
            if (!password_verify($_POST['current_password'], $user->password)) {
                $this->flash('error', 'Password saat ini salah');
                return $this->back();
            }
            
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }
        }

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['avatar'], 'uploads/avatars');
            if ($result['success']) {
                // Delete old avatar
                if ($user->avatar) {
                    delete_file('uploads/avatars/' . $user->avatar);
                }
                $data['avatar'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        $db->table('users')->where('id', $user->id)->update($data);
        
        log_activity('profile_update', 'Profile updated', 'user', $user->id);
        $this->flash('success', 'Profil berhasil diperbarui');
        return $this->redirect(url('profile'));
    }
}