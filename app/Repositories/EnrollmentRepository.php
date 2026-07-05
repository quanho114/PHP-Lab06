<?php

namespace App\Repositories;

use PDO;
use App\Core\DuplicateRecordException;
use PDOException;

class EnrollmentRepository
{
    private PDO $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::getInstance();
    }

    public function countAll(string $keyword = '', string $payment_status = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM enrollments WHERE deleted_at IS NULL";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (enrollment_code LIKE :keyword1 OR student_name LIKE :keyword2 OR student_email LIKE :keyword3)";
            $params['keyword1'] = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
        }
        if ($payment_status !== '') {
            $sql .= " AND payment_status = :payment_status";
            $params['payment_status'] = $payment_status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, string $payment_status, int $limit, int $offset, string $sort = 'created_at', string $direction = 'DESC'): array
    {
        $allowedSort = ['id', 'enrollment_code', 'student_name', 'student_email', 'course_fee', 'payment_status', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'created_at';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM enrollments WHERE deleted_at IS NULL";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (enrollment_code LIKE :keyword1 OR student_name LIKE :keyword2 OR student_email LIKE :keyword3)";
            $params['keyword1'] = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
        }
        if ($payment_status !== '') {
            $sql .= " AND payment_status = :payment_status";
            $params['payment_status'] = $payment_status;
        }

        $sql .= " ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO enrollments (enrollment_code, student_name, student_email, course_fee, payment_status)
                    VALUES (:enrollment_code, :student_name, :student_email, :course_fee, :payment_status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'enrollment_code' => $data['enrollment_code'],
                'student_name' => $data['student_name'],
                'student_email' => $data['student_email'] ?? null,
                'course_fee' => $data['course_fee'] ?? 0.00,
                'payment_status' => $data['payment_status'] ?? 'unpaid'
            ]);

            $enrollmentId = (int)$this->db->lastInsertId();

            // Insert payment record if payment_status is 'paid'
            if (($data['payment_status'] ?? 'unpaid') === 'paid') {
                $paySql = "INSERT INTO payments (enrollment_id, amount, payment_method)
                           VALUES (:enrollment_id, :amount, :payment_method)";
                $payStmt = $this->db->prepare($paySql);
                $payStmt->execute([
                    'enrollment_id' => $enrollmentId,
                    'amount' => $data['course_fee'] ?? 0.00,
                    'payment_method' => 'cash'
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                throw new DuplicateRecordException('Mã phiếu đăng ký này đã tồn tại trong hệ thống.');
            }
            throw $e;
        }
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['id' => $id]);
        $enrollment = $stmt->fetch();
        return $enrollment ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE enrollments 
                SET enrollment_code = :enrollment_code, student_name = :student_name, 
                    student_email = :student_email, course_fee = :course_fee, 
                    payment_status = :payment_status, updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                'id' => $id,
                'enrollment_code' => $data['enrollment_code'],
                'student_name' => $data['student_name'],
                'student_email' => $data['student_email'] ?? null,
                'course_fee' => $data['course_fee'] ?? 0.00,
                'payment_status' => $data['payment_status'] ?? 'unpaid'
            ]);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                throw new DuplicateRecordException('Mã phiếu đăng ký này đã tồn tại trong hệ thống.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE enrollments SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function findLatestEnrollmentCode(): ?string
    {
        $stmt = $this->db->query("SELECT enrollment_code FROM enrollments WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1");
        $code = $stmt->fetchColumn();
        return $code ?: null;
    }

    public function getRevenueSum(): float
    {
        $stmt = $this->db->query("SELECT SUM(course_fee) FROM enrollments WHERE payment_status = 'paid' AND deleted_at IS NULL");
        return (float)($stmt->fetchColumn() ?: 0.00);
    }
}

