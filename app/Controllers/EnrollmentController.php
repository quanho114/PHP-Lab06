<?php

namespace App\Controllers;

use App\Services\EnrollmentService;

class EnrollmentController
{
    private EnrollmentService $service;

    public function __construct(EnrollmentService $service = null)
    {
        $this->service = $service ?? new EnrollmentService();
    }

    public function index(): void
    {
        require_login();
        $data = $this->service->getEnrollmentList($_GET);
        render('enrollments/index', array_merge(['title' => 'Quản Lý Đăng Ký & Học Phí'], $data));
    }

    public function create(): void
    {
        require_login();
        $autoCode = $this->service->generateEnrollmentCode();
        render('enrollments/create', [
            'title' => 'Thêm Phiếu Đăng Ký Học Viên',
            'autoCode' => $autoCode
        ]);
    }

    public function store(): void
    {
        require_login();
        
        $result = $this->service->createEnrollment($_POST);
        if (!$result['success']) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['old_input'] = $_POST;
            
            $autoCode = $this->service->generateEnrollmentCode();
            render('enrollments/create', [
                'title' => 'Thêm Phiếu Đăng Ký Học Viên',
                'autoCode' => $autoCode
            ]);
            return;
        }

        flash('success', 'Phiếu đăng ký học viên đã được tạo thành công.');
        redirect('/enrollments');
    }

    public function edit(): void
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $enrollment = $this->service->findEnrollmentById($id);
        if (!$enrollment) {
            flash('error', 'Phiếu đăng ký học viên không tồn tại.');
            redirect('/enrollments');
        }

        render('enrollments/edit', [
            'title' => 'Sửa Phiếu Đăng Ký',
            'enrollment' => $enrollment
        ]);
    }

    public function update(): void
    {
        require_login();
        $id = (int)($_POST['id'] ?? 0);
        
        $result = $this->service->updateEnrollment($id, $_POST);
        if (!$result['success']) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['old_input'] = $_POST;
            
            $enrollment = $this->service->findEnrollmentById($id);
            render('enrollments/edit', [
                'title' => 'Sửa Phiếu Đăng Ký',
                'enrollment' => $enrollment ?: []
            ]);
            return;
        }

        flash('success', 'Phiếu đăng ký đã được cập nhật thành công.');
        redirect('/enrollments');
    }

    public function delete(): void
    {
        require_login();
        $id = (int)($_POST['id'] ?? 0);
        
        $result = $this->service->deleteEnrollment($id);
        if (!$result['success']) {
            flash('error', $result['errors']['general'] ?? 'Có lỗi xảy ra khi xóa phiếu đăng ký.');
        } else {
            flash('success', 'Phiếu đăng ký đã được xóa thành công.');
        }
        redirect('/enrollments');
    }
}
