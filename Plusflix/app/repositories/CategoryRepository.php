<?php
class CategoryRepository
{
    public function all(): array
    {
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Category((int)$r['id'], $r['name']), $rows);
    }

    public function findOrCreateByName(string $name): int
    {
        $pdo = DB::conn();
        $pdo->prepare("INSERT OR IGNORE INTO categories(name) VALUES(:name)")->execute([':name' => $name]);
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->execute([':name' => $name]);
        $id = $stmt->fetchColumn();
        return (int)$id;
    }

    public function namesForTitle(int $titleId): array
    {
        $pdo = DB::conn();
        $sql = "SELECT c.name
                FROM categories c
                JOIN title_category tc ON tc.category_id = c.id
                WHERE tc.title_id = :id
                ORDER BY c.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $titleId]);
        return array_map(fn($r) => $r['name'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function setForTitle(int $titleId, array $names): void
    {
        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM title_category WHERE title_id=:id")->execute([':id' => $titleId]);
            foreach ($names as $n) {
                $cid = $this->findOrCreateByName($n);
                $pdo->prepare("INSERT OR IGNORE INTO title_category(title_id, category_id) VALUES(:t,:c)")
                    ->execute([':t' => $titleId, ':c' => $cid]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
