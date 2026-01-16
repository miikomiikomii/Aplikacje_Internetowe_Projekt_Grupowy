<?php
class DB
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo) return self::$pdo;

        $dbPath = __DIR__ . '/../../data/plusflix.db';
        $dsn = 'sqlite:' . $dbPath;

        self::$pdo = new PDO($dsn);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->exec("PRAGMA foreign_keys = ON;");

        self::ensureInitialized(self::$pdo);

        return self::$pdo;
    }

    private static function ensureInitialized(PDO $pdo): void
    {
        $hasTitlesTable = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='titles'")->fetchColumn();
        if ($hasTitlesTable) return;

        $schema = file_get_contents(__DIR__ . '/../../data/schema.sql');
        $pdo->exec($schema);

        $seed = file_get_contents(__DIR__ . '/../../data/seed.sql');
        $pdo->exec($seed);
    }

    public static function reset(): void
    {

        if (self::$pdo !== null) {
            self::$pdo = null;
        }

        $dbPath = __DIR__ . '/../../data/plusflix.db';

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        self::conn();
    }
}
