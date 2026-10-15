<?php

namespace ObservaStage;

use PDO;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=observastage',
                getenv('DB_USER') ?: 'observastage',
                getenv('DB_PASSWORD') ?: 'observastage',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$pdo;
    }

    /** @return list<array<string, mixed>> */
    public static function lignes(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function ligne(string $sql, array $params = []): ?array
    {
        return self::lignes($sql, $params)[0] ?? null;
    }

    public static function valeur(string $sql, array $params = []): mixed
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
