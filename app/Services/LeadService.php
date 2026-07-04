<?php

namespace App\Services;

use App\Repositories\LeadRepository;
use App\Core\DuplicateRecordException;

class LeadService
{
    private LeadRepository $repo;

    public function __construct(LeadRepository $repo = null)
    {
        $this->repo = $repo ?? new LeadRepository();
    }

    public function getLeadList(array $query): array
    {
        $keyword = trim($query['q'] ?? '');
        $status = trim($query['status'] ?? '');
        
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 10;

        $sort = trim($query['sort'] ?? 'created_at');
        $direction = trim($query['direction'] ?? 'desc');

        $totalItems = $this->repo->countAll($keyword, $status);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'leads' => $this->repo->getPaginated($keyword, $status, $perPage, $offset, $sort, $direction),
            'keyword' => $keyword,
            'status' => $status,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    public function validateLeadData(array $input): array
    {
        $errors = [];
        $fullname = trim($input['fullname'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $status = trim($input['status'] ?? 'new');
        $interested_course = trim($input['interested_course'] ?? '');
        $note = trim($input['note'] ?? '');

        if ($fullname === '') {
            $errors['fullname'] = 'Tên lead không được để trống.';
        }
        
        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if (!in_array($status, ['new', 'contacted', 'enrolled', 'lost'], true)) {
            $errors['status'] = 'Trạng thái lead không hợp lệ.';
        }

        return [
            'errors' => $errors,
            'values' => compact('fullname', 'email', 'phone', 'status', 'interested_course', 'note')
        ];
    }

    public function createLead(array $input): array
    {
        $validation = $this->validateLeadData($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        try {
            $this->repo->create($validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']
            ];
        }
    }

    public function updateLead(int $id, array $input): array
    {
        if (!$this->repo->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Lead không tồn tại.']];
        }

        $validation = $this->validateLeadData($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        try {
            $this->repo->update($id, $validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']
            ];
        }
    }

    public function deleteLead(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->repo->delete($id);
        return ['success' => true, 'errors' => []];
    }

    public function findLeadById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function getTotalLeadsCount(): int
    {
        return $this->repo->countAll();
    }
}
