<?php

namespace App\Services;

use App\Repositories\EnrollmentRepository;
use App\Core\DuplicateRecordException;

class EnrollmentService
{
    private EnrollmentRepository $repo;

    public function __construct(EnrollmentRepository $repo = null)
    {
        $this->repo = $repo ?? new EnrollmentRepository();
    }

    public function getEnrollmentList(array $query): array
    {
        $keyword = trim($query['q'] ?? '');
        $payment_status = trim($query['payment_status'] ?? '');
        
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = 5;

        $sort = trim($query['sort'] ?? 'created_at');
        $direction = trim($query['direction'] ?? 'desc');

        $totalItems = $this->repo->countAll($keyword, $payment_status);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'enrollments' => $this->repo->getPaginated($keyword, $payment_status, $perPage, $offset, $sort, $direction),
            'keyword' => $keyword,
            'payment_status' => $payment_status,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    public function generateEnrollmentCode(): string
    {
        $latest = $this->repo->findLatestEnrollmentCode();
        $year = date('Y');
        if ($latest && preg_match('/^ENR-\d{4}-(\d+)$/', $latest, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
            return sprintf('ENR-%d-%04d', $year, $nextSeq);
        }
        return sprintf('ENR-%d-0001', $year);
    }

    public function validateEnrollmentData(array $input): array
    {
        $errors = [];
        $enrollment_code = trim($input['enrollment_code'] ?? '');
        $student_name = trim($input['student_name'] ?? '');
        $student_email = trim($input['student_email'] ?? '');
        $course_fee = filter_var($input['course_fee'] ?? 0.00, FILTER_VALIDATE_FLOAT);
        $payment_status = trim($input['payment_status'] ?? 'unpaid');

        if ($enrollment_code === '') {
            $errors['enrollment_code'] = 'Mã đăng ký học viên không được để trống.';
        } elseif (!preg_match('/^ENR-\d{4}-\d{4,6}$/', $enrollment_code)) {
            $errors['enrollment_code'] = 'Mã đăng ký phải đúng định dạng (Ví dụ: ENR-2026-0001).';
        }

        if ($student_name === '') {
            $errors['student_name'] = 'Tên học viên không được để trống.';
        }

        if ($student_email !== '' && !filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
            $errors['student_email'] = 'Email học viên không đúng định dạng.';
        }

        if ($course_fee === false || $course_fee < 0) {
            $errors['course_fee'] = 'Học phí phải là số lớn hơn hoặc bằng 0.';
        }

        if (!in_array($payment_status, ['unpaid', 'paid', 'refunded', 'cancelled'], true)) {
            $errors['payment_status'] = 'Trạng thái thanh toán không hợp lệ.';
        }

        return [
            'errors' => $errors,
            'values' => compact('enrollment_code', 'student_name', 'student_email', 'course_fee', 'payment_status')
        ];
    }

    public function createEnrollment(array $input): array
    {
        $validation = $this->validateEnrollmentData($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        try {
            $this->repo->create($validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return [
                'success' => false,
                'errors' => ['enrollment_code' => 'Mã phiếu đăng ký này đã tồn tại trong hệ thống.']
            ];
        }
    }

    public function updateEnrollment(int $id, array $input): array
    {
        if (!$this->repo->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Phiếu đăng ký không tồn tại.']];
        }

        $validation = $this->validateEnrollmentData($input);
        if (!empty($validation['errors'])) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        try {
            $this->repo->update($id, $validation['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return [
                'success' => false,
                'errors' => ['enrollment_code' => 'Mã phiếu đăng ký này đã tồn tại trong hệ thống.']
            ];
        }
    }

    public function deleteEnrollment(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->repo->delete($id);
        return ['success' => true, 'errors' => []];
    }

    public function findEnrollmentById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function getTotalEnrollmentsCount(): int
    {
        return $this->repo->countAll();
    }

    public function getRevenueSum(): float
    {
        return $this->repo->getRevenueSum();
    }
}

