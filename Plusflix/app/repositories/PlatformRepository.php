<?php
class PlatformRepository
{
    public function all(): array
    {
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT id, name FROM platforms ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Platform((int)$r['id'], $r['name']), $rows);
    }

    public function findOrCreateByName(string $name): int
    {
        $pdo = DB::conn();
        $pdo->prepare("INSERT OR IGNORE INTO platforms(name) VALUES(:name)")->execute([':name' => $name]);
        $id = $pdo->query("SELECT id FROM platforms WHERE name=" . $pdo->quote($name))->fetchColumn();
        return (int)$id;
    }

    public function namesForTitle(int $titleId): array
    {
        $pdo = DB::conn();
        $sql = "SELECT p.name
                FROM platforms p
                JOIN title_platform tp ON tp.platform_id = p.id
                WHERE tp.title_id = :id
                ORDER BY p.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $titleId]);
        return array_map(fn($r) => $r['name'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function setForTitle(int $titleId, array $names): void
    {
        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM title_platform WHERE title_id=:id")->execute([':id' => $titleId]);
            foreach ($names as $n) {
                $pid = $this->findOrCreateByName($n);
                $pdo->prepare("INSERT OR IGNORE INTO title_platform(title_id, platform_id) VALUES(:t,:p)")
                    ->execute([':t' => $titleId, ':p' => $pid]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
