<?php

namespace App\Repositories;

use PDO;
use App\Core\DuplicateRecordException;
use PDOException;

class LeadRepository
{
    private PDO $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? \App\Core\Database::getInstance();
    }

    public function countAll(string $keyword = '', string $status = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM course_leads WHERE deleted_at IS NULL";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (fullname LIKE :keyword1 OR email LIKE :keyword2 OR phone LIKE :keyword3 OR interested_course LIKE :keyword4)";
            $params['keyword1'] = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
            $params['keyword4'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, string $status, int $limit, int $offset, string $sort = 'created_at', string $direction = 'DESC'): array
    {
        $allowedSort = ['id', 'fullname', 'email', 'phone', 'status', 'interested_course', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'created_at';
        }

        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM course_leads WHERE deleted_at IS NULL";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (fullname LIKE :keyword1 OR email LIKE :keyword2 OR phone LIKE :keyword3 OR interested_course LIKE :keyword4)";
            $params['keyword1'] = '%' . $keyword . '%';
            $params['keyword2'] = '%' . $keyword . '%';
            $params['keyword3'] = '%' . $keyword . '%';
            $params['keyword4'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
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
        $sql = "INSERT INTO course_leads (fullname, email, phone, status, interested_course, note)
                VALUES (:fullname, :email, :phone, :status, :interested_course, :note)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'new',
                'interested_course' => $data['interested_course'] ?? null,
                'note' => $data['note'] ?? null
            ]);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                throw new DuplicateRecordException('Email lead đã tồn tại trong hệ thống.');
            }
            throw $e;
        }
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM course_leads WHERE id = :id AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['id' => $id]);
        $lead = $stmt->fetch();
        return $lead ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE course_leads 
                SET fullname = :fullname, email = :email, phone = :phone, 
                    status = :status, interested_course = :interested_course, note = :note, 
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                'id' => $id,
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'new',
                'interested_course' => $data['interested_course'] ?? null,
                'note' => $data['note'] ?? null
            ]);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                throw new DuplicateRecordException('Email lead đã tồn tại trong hệ thống.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE course_leads SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
