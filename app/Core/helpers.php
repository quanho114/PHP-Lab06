<?php

use App\Services\CSRFService;

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('render')) {
    function render(string $view, array $data = []): void {
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        extract($data);

        // Buffering child view
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}

if (!function_exists('partial')) {
    function partial(string $name, array $data = []): void {
        extract($data);
        require __DIR__ . '/../Views/partials/' . $name . '.php';
    }
}

if (!function_exists('flash')) {
    function flash(string $key, string $message): void {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('old')) {
    function old(string $key, array $old = [], string $default = ''): string {
        return (string)($old[$key] ?? $default);
    }
}

if (!function_exists('require_login')) {
    function require_login(): void {
        // Timeout check: 10 minutes (600 seconds)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            session_start();
            flash('error', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            redirect('/login');
        }

        if (!isset($_SESSION['user_id'])) {
            flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('/login');
        }
        
        // Update activity timestamp
        $_SESSION['last_activity'] = time();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = CSRFService::getToken();
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('log_message')) {
    function log_message(string $msg): void {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/app.log';
        $time = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$time] $msg\n", FILE_APPEND);
    }
}
