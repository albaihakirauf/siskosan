<?php

require_once __DIR__ . '/../Models/Notification.php';

abstract class Controller {
    protected $data = [];

    public function __construct() {
        $this->data['auth'] = auth();
        $this->data['app_name'] = config('app.name');
        $this->data['currency_symbol'] = config('app.currency.symbol');

        $authUser = $this->data['auth'];
        if ($authUser) {
            try {
                $this->data['unreadCount'] = Notification::getUnreadCount($authUser->id);
                $this->data['notifications'] = Notification::where('user_id', $authUser->id)
                    ->orderBy('created_at', 'DESC')
                    ->limit(8)
                    ->get();
            } catch (\Throwable $e) {
                $this->data['unreadCount'] = 0;
                $this->data['notifications'] = [];
            }
        } else {
            $this->data['unreadCount'] = 0;
            $this->data['notifications'] = [];
        }
    }

    protected function view($path, $data = []) {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        $layout = $this->data['layout'] ?? 'layouts.app';
        $viewFile = view_path(str_replace('.', '/', $path) . '.php');
        $layoutFile = view_path(str_replace('.', '/', $layout) . '.php');
        
        if (!file_exists($viewFile)) {
            abort(500, "View not found: {$path}");
        }
        
        if (file_exists($layoutFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            include $layoutFile;
        } else {
            include $viewFile;
        }
    }

    protected function redirect($url, $statusCode = 302) {
        redirect($url, $statusCode);
    }

    protected function back() {
        back();
    }

    protected function json($data, $statusCode = 200) {
        json_response($data, $statusCode);
    }

    protected function validate($data, $rules, $messages = []) {
        $errors = validate($data, $rules, $messages);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            return false;
        }
        return true;
    }

    protected function validationErrors() {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $errors;
    }

    protected function old($key, $default = '') {
        return old($key, $default);
    }

    protected function flash($key, $message = null) {
        return flash($key, $message);
    }

    protected function hasFlash($key) {
        return has_flash($key);
    }

    protected function abort($code, $message = '') {
        abort($code, $message);
    }

    protected function authorize($permission) {
        Auth::authorize($permission);
    }
}