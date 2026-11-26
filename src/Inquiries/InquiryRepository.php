<?php

declare(strict_types=1);

namespace App\Inquiries;

use App\Database\Connection;
use InvalidArgumentException;
use PDO;

/**
 * Data access for the `inquiries` table. Prepared statements only; identifiers are whitelisted.
 */
final class InquiryRepository
{
    public const TYPES    = ['enrolment', 'booking', 'contact'];
    public const STATUSES = ['new', 'read', 'archived'];

    /**
     * @param array{type:string,name:string,email:string,phone:?string,subject:string,message:?string,details:array<string,mixed>,ip_hash:?string} $data
     */
    public static function create(array $data): int
    {
        if (!in_array($data['type'], self::TYPES, true)) {
            throw new InvalidArgumentException('Invalid inquiry type.');
        }

        $pdo  = Connection::fromEnv();
        $stmt = $pdo->prepare(
            'INSERT INTO inquiries (type, name, email, phone, subject, message, details, ip_hash)
             VALUES (:type, :name, :email, :phone, :subject, :message, :details, :ip_hash)'
        );
        $stmt->execute([
            'type'    => $data['type'],
            'name'    => mb_substr($data['name'], 0, 150),
            'email'   => $data['email'],
            'phone'   => $data['phone'] !== null && $data['phone'] !== '' ? $data['phone'] : null,
            'subject' => mb_substr($data['subject'], 0, 255),
            'message' => $data['message'] !== null && $data['message'] !== '' ? $data['message'] : null,
            'details' => json_encode($data['details'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip_hash' => $data['ip_hash'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function countRecentFromIp(string $ipHash, int $minutes): int
    {
        $stmt = Connection::fromEnv()->prepare(
            'SELECT COUNT(*) FROM inquiries WHERE ip_hash = :ip AND created_at > (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue('ip', $ipHash);
        $stmt->bindValue('minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function recordEmail(int $id, ?string $error): void
    {
        $sql = $error === null
            ? 'UPDATE inquiries SET email_sent_at = NOW(), email_error = NULL WHERE id = :id'
            : 'UPDATE inquiries SET email_error = :error WHERE id = :id';
        $params = ['id' => $id];
        if ($error !== null) {
            $params['error'] = mb_substr($error, 0, 255);
        }
        Connection::fromEnv()->prepare($sql)->execute($params);
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int}
     */
    public static function paginate(?string $type, ?string $status, int $page, int $perPage = 25): array
    {
        $where  = [];
        $params = [];
        if ($type !== null) {
            if (!in_array($type, self::TYPES, true)) {
                throw new InvalidArgumentException('Invalid type filter.');
            }
            $where[] = 'type = :type';
            $params['type'] = $type;
        }
        if ($status !== null) {
            if (!in_array($status, self::STATUSES, true)) {
                throw new InvalidArgumentException('Invalid status filter.');
            }
            $where[] = 'status = :status';
            $params['status'] = $status;
        } else {
            $where[] = "status <> 'archived'";
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $pdo   = Connection::fromEnv();
        $count = $pdo->prepare("SELECT COUNT(*) FROM inquiries {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT id, type, status, name, email, subject, created_at, email_sent_at, email_error
             FROM inquiries {$whereSql} ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $stmt->execute();

        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Connection::fromEnv()->prepare('SELECT * FROM inquiries WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        $row['details'] = json_decode((string) $row['details'], true) ?: [];

        return $row;
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid status.');
        }
        Connection::fromEnv()->prepare('UPDATE inquiries SET status = :status WHERE id = :id')
            ->execute(['status' => $status, 'id' => $id]);
    }

    /** @return array<string, int> count of 'new' inquiries per type */
    public static function newCounts(): array
    {
        $rows = Connection::fromEnv()
            ->query("SELECT type, COUNT(*) AS n FROM inquiries WHERE status = 'new' GROUP BY type")
            ->fetchAll();
        $counts = array_fill_keys(self::TYPES, 0);
        foreach ($rows as $r) {
            $counts[$r['type']] = (int) $r['n'];
        }

        return $counts;
    }
}
