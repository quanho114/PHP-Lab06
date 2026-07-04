<?php

namespace App\Controllers;

use App\Services\LeadService;

class HomeController
{
    private LeadService $leadService;

    public function __construct(LeadService $leadService = null)
    {
        $this->leadService = $leadService ?? new LeadService();
    }

    public function index(): void
    {
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
        }

        // Get flashed errors or old input for the public form
        $errors = $_SESSION['flash_errors'] ?? [];
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_errors'], $_SESSION['flash_old']);

        render('home/index', [
            'title' => 'Đăng ký tư vấn khóa học',
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function publicStore(): void
    {
        // 1. Honeypot check
        $honeypot = $_POST['website'] ?? '';
        if ($honeypot !== '') {
            // Silently redirect to homepage to discard bot spam
            flash('success', 'Đăng ký tư vấn thành công! Chúng tôi sẽ liên hệ lại sớm.');
            redirect('/');
        }

        // 2. Rate limit check (5-second threshold)
        $lastSubmit = $_SESSION['last_submit_time'] ?? 0;
        if (time() - $lastSubmit < 5) {
            $_SESSION['flash_errors'] = ['general' => 'Hành vi gửi biểu mẫu quá nhanh. Vui lòng đợi 5 giây giữa các lần đăng ký.'];
            $_SESSION['flash_old'] = $_POST;
            redirect('/');
        }
        $_SESSION['last_submit_time'] = time();

        // 3. Process creation via LeadService
        // Set default status as 'new' for public guest submissions
        $data = $_POST;
        $data['status'] = 'new';
        
        $result = $this->leadService->createLead($data);
        if (!$result['success']) {
            $_SESSION['flash_errors'] = $result['errors'];
            $_SESSION['flash_old'] = $_POST;
            redirect('/');
        }

        flash('success', 'Đăng ký tư vấn thành công! Chúng tôi sẽ liên hệ lại với bạn sớm.');
        redirect('/');
    }
}
