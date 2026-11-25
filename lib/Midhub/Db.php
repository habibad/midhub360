<?php
namespace Midhub;

/**
 * Thin prepared-statement layer over the shared mysqli connection.
 *
 * Every method throws \RuntimeException on failure, on PHP 7.4 (where mysqli returns
 * false) and on PHP 8.1+ (where mysqli throws mysqli_sql_exception) alike. The global
 * mysqli_report() mode is deliberately left untouched so legacy code keeps working.
 */
final class Db
{
    /** @var int */
    private static $txDepth = 0;

    public static function conn(): \mysqli
    {
        global $connect;
        if (!($connect instanceof \mysqli)) {
            $connect = midhub_get_db_connection();
        }
        return $connect;
    }

    /**
     * Prepare + bind + execute. Returns the executed statement.
     * $types follows mysqli conventions: i = int, d = double, s = string, b = blob.
     */
    public static function run(string $sql, string $types = '', array $params = array()): \mysqli_stmt
    {
        $db = self::conn();
        try {
            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                throw new \RuntimeException('DB prepare failed: ' . $db->error);
            }
            if ($types !== '') {
                if (strlen($types) !== count($params)) {
                    throw new \RuntimeException('DB bind mismatch: ' . strlen($types) . ' types for ' . count($params) . ' params');
                }
                $params = array_values($params);
                $refs = array();
                foreach ($params as $k => $v) {
                    $refs[$k] = &$params[$k];
                }
                if (!$stmt->bind_param($types, ...$refs)) {
                    throw new \RuntimeException('DB bind failed: ' . $stmt->error);
                }
            }
            if (!$stmt->execute()) {
                throw new \RuntimeException('DB execute failed: ' . $stmt->error);
            }
            return $stmt;
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException('DB error: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, string $types = '', array $params = array())
    {
        $stmt = self::run($sql, $types, $params);
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, string $types = '', array $params = array()): array
    {
        $stmt = self::run($sql, $types, $params);
        $res = $stmt->get_result();
        $rows = array();
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $rows[] = $r;
            }
        }
        $stmt->close();
        return $rows;
    }

    /** @return mixed|null first column of first row */
    public static function scalar(string $sql, string $types = '', array $params = array())
    {
        $row = self::one($sql, $types, $params);
        return $row ? reset($row) : null;
    }

    /** Executes a write and returns affected rows. */
    public static function exec(string $sql, string $types = '', array $params = array()): int
    {
        $stmt = self::run($sql, $types, $params);
        $n = $stmt->affected_rows;
        $stmt->close();
        return (int) $n;
    }

    /** Executes an INSERT and returns the new auto-increment id. */
    public static function insert(string $sql, string $types = '', array $params = array()): int
    {
        $stmt = self::run($sql, $types, $params);
        $id = $stmt->insert_id;
        $stmt->close();
        return (int) $id;
    }

    /** Raw statement without parameters (DDL in migrations). */
    public static function raw(string $sql)
    {
        $db = self::conn();
        try {
            $r = $db->query($sql);
        } catch (\mysqli_sql_exception $e) {
            throw new \RuntimeException('DB error: ' . $e->getMessage() . ' | SQL: ' . substr($sql, 0, 300), 0, $e);
        }
        if ($r === false) {
            throw new \RuntimeException('DB error: ' . $db->error . ' | SQL: ' . substr($sql, 0, 300));
        }
        return $r;
    }

    public static function inTransaction(): bool
    {
        return self::$txDepth > 0;
    }

    /**
     * Runs $fn inside a transaction (nested calls join the outer transaction).
     * Commits on return, rolls back and rethrows on any exception.
     *
     * @return mixed whatever $fn returns
     */
    public static function transaction(callable $fn)
    {
        $db = self::conn();
        if (self::$txDepth === 0) {
            $db->begin_transaction();
        }
        self::$txDepth++;
        try {
            $result = $fn();
            self::$txDepth--;
            if (self::$txDepth === 0) {
                if (!$db->commit()) {
                    throw new \RuntimeException('DB commit failed: ' . $db->error);
                }
            }
            return $result;
        } catch (\Throwable $e) {
            self::$txDepth--;
            if (self::$txDepth === 0) {
                $db->rollback();
            }
            throw $e;
        }
    }
}
