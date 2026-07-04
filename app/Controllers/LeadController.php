<?php

namespace App\Controllers;

use App\Services\LeadService;

class LeadController
{
    private LeadService $service;

    public function __construct(LeadService $service = null)
    {
        $this->service = $service ?? new LeadService();
    }

    public function index(): void
    {
        require_login();
        $data = $this->service->getLeadList($_GET);
        render('leads/index', array_merge(['title' => 'Quản Lý Course Leads'], $data));
    }

    public function create(): void
    {
        require_login();
        render('leads/create', [
            'title' => 'Thêm Lead Mới'
        ]);
    }

    public function store(): void
    {
        require_login();
        
        $result = $this->service->createLead($_POST);
        if (!$result['success']) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['old_input'] = $_POST;
            render('leads/create', [
                'title' => 'Thêm Lead Mới'
            ]);
            return;
        }

        flash('success', 'Lead đã được tạo thành công.');
        redirect('/leads');
    }

    public function edit(): void
    {
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $lead = $this->service->findLeadById($id);
        if (!$lead) {
            flash('error', 'Lead không tồn tại.');
            redirect('/leads');
        }

        render('leads/edit', [
            'title' => 'Sửa Course Lead',
            'lead' => $lead
        ]);
    }

    public function update(): void
    {
        require_login();
        $id = (int)($_POST['id'] ?? 0);
        
        $result = $this->service->updateLead($id, $_POST);
        if (!$result['success']) {
            $_SESSION['errors'] = $result['errors'];
            $_SESSION['old_input'] = $_POST;
            
            $lead = $this->service->findLeadById($id);
            render('leads/edit', [
                'title' => 'Sửa Course Lead',
                'lead' => $lead ?: []
            ]);
            return;
        }

        flash('success', 'Lead đã được cập nhật thành công.');
        redirect('/leads');
    }

    public function delete(): void
    {
        require_login();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            flash('error', 'Quyền truy cập bị từ chối: Chỉ tài khoản Admin mới có quyền xóa lead.');
            redirect('/leads');
            return;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        
        $result = $this->service->deleteLead($id);
        if (!$result['success']) {
            flash('error', $result['errors']['general'] ?? 'Có lỗi xảy ra khi xóa lead.');
        } else {
            flash('success', 'Lead đã được xóa thành công.');
        }
        redirect('/leads');
    }
}
