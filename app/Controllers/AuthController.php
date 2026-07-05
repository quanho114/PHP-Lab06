<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    public function login(): void
    {
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
        }

        render('auth/login', [
            'title' => 'Đăng Nhập CRM'
        ]);
    }

    public function handleLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (empty($email)) {
            $errors['email'] = 'Email không được để trống.';
        }
        if (empty($password)) {
            $errors['password'] = 'Mật khẩu không được để trống.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            render('auth/login', [
                'title' => 'Đăng Nhập CRM'
            ]);
            return;
        }

        if ($this->authService->login($email, $password)) {
            flash('success', 'Đăng nhập hệ thống thành công!');
            redirect('/dashboard');
        }

        // Authentication failed
        log_message("Failed login attempt for email: " . $email);
        $errors['general'] = 'Email hoặc mật khẩu không đúng.';
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = ['email' => $email];
        render('auth/login', [
            'title' => 'Đăng Nhập CRM'
        ]);
    }

    public function logout(): void
    {
        $this->authService->logout();
        session_start();
        flash('success', 'Bạn đã đăng xuất thành công.');
        redirect('/login');
    }
}
