<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Auth.php';

class AuthController extends Controller {
    public function showLogin() {
        if (auth_check()) {
            return $this->redirect(url('dashboard'));
        }
        $this->data['layout'] = 'layouts.auth';
        $this->view('auth.login');
    }

    public function login() {
        if (auth_check()) {
            return $this->redirect(url('dashboard'));
        }

        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        if (Auth::attempt($email, $password, $remember)) {
            $user = auth();
            log_activity('login', 'User logged in');
            
            // Redirect based on role
            switch ($user->role) {
                case 'owner':
                case 'admin':
                    return $this->redirect(url('dashboard'));
                case 'tenant':
                    return $this->redirect(url('dashboard'));
                default:
                    return $this->redirect(url('dashboard'));
            }
        }

        $this->flash('error', 'Email atau password salah');
        return $this->back();
    }

    public function showRegister() {
        if (auth_check()) {
            return $this->redirect(url('dashboard'));
        }
        $this->data['layout'] = 'layouts.auth';
        $this->view('auth.register');
    }

    public function register() {
        if (auth_check()) {
            return $this->redirect(url('dashboard'));
        }

        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'max:20',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $data = [
            'name' => trim($_POST['name']),
            'email' => strtolower(trim($_POST['email'])),
            'password' => $_POST['password'],
            'phone' => $_POST['phone'] ?? null,
            'role' => 'tenant',
        ];

        $result = Auth::register($data);

        if ($result['success']) {
            log_activity('register', 'New user registered', 'user', $result['user_id']);
            $this->flash('success', 'Registrasi berhasil! Silakan login.');
            return $this->redirect(url('login'));
        }

        $this->flash('error', $result['message']);
        return $this->back();
    }

    public function logout() {
        $user = auth();
        log_activity('logout', 'User logged out');
        Auth::logout();
        $this->flash('success', 'Berhasil logout');
        return $this->redirect(url('login'));
    }

    public function showForgotPassword() {
        $this->data['layout'] = 'layouts.auth';
        $this->view('auth.forgot-password');
    }

    public function forgotPassword() {
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $email = $_POST['email'] ?? '';
        
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email tidak valid');
            return $this->back();
        }

        $db = db();
        $user = $db->table('users')->where('email', $email)->first();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $db->table('users')->where('id', $user['id'])->update([
                'remember_token' => $token,
            ]);

            // In real app, send email with reset link
            // For now, just show the token
            $this->flash('success', 'Link reset password telah dikirim ke email Anda. (Token: ' . $token . ')');
            log_activity('password_reset_request', 'Password reset requested', 'user', $user['id']);
        } else {
            // Don't reveal if email exists
            $this->flash('success', 'Jika email terdaftar, link reset akan dikirim.');
        }

        return $this->redirect(url('login'));
    }

    public function showResetPassword($token) {
        $db = db();
        $user = $db->table('users')->where('remember_token', $token)->first();

        if (!$user) {
            $this->flash('error', 'Token reset tidak valid atau sudah kadaluarsa');
            return $this->redirect(url('login'));
        }

        $this->data['layout'] = 'layouts.auth';
        $this->data['token'] = $token;
        $this->data['form_action'] = url('reset-password');
        $this->view('auth.reset-password');
    }

    public function resetPassword() {
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $rules = [
            'password' => 'required|min:6|confirmed',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $db = db();
        $user = $db->table('users')->where('remember_token', $token)->first();

        if (!$user) {
            $this->flash('error', 'Token reset tidak valid atau sudah kadaluarsa');
            return $this->redirect(url('login'));
        }

        $db->table('users')->where('id', $user['id'])->update([
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'remember_token' => null,
        ]);

        log_activity('password_reset', 'Password reset completed', 'user', $user['id']);
        $this->flash('success', 'Password berhasil direset. Silakan login.');
        return $this->redirect(url('login'));
    }
}