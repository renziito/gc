<?php
declare(strict_types=1);
final class Database {
    private static ?PDO $pdo = null;
    public static function connection(array $config): PDO {
        if (self::$pdo) return self::$pdo;
        $d = $config['database'];
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $d['host'], $d['name'], $d['charset'] ?? 'utf8mb4');
        self::$pdo = new PDO($dsn, $d['user'], $d['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }
}
