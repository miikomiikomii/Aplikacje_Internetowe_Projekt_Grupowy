<?php
class TitlesRepository
{
    private CategoryRepository $catRepo;
    private PlatformRepository $platRepo;

    public function __construct()
    {
        $this->catRepo = new CategoryRepository();
        $this->platRepo = new PlatformRepository();
    }

    public function search(array $filters = []): array
    {
        $q = trim((string)($filters['q'] ?? ''));
        $type = trim((string)($filters['type'] ?? ''));
        $year = (int)($filters['year'] ?? 0);
        $category = trim((string)($filters['category'] ?? ''));
        $platform = trim((string)($filters['platform'] ?? ''));
        $sort = trim((string)($filters['sort'] ?? 'newest'));

        $pdo = DB::conn();

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = "(t.name LIKE :q OR t.description LIKE :q
                OR EXISTS (
                    SELECT 1 FROM title_category tc
                    JOIN categories c ON c.id = tc.category_id
                    WHERE tc.title_id = t.id AND c.name LIKE :q
                )
                OR EXISTS (
                    SELECT 1 FROM title_platform tp
                    JOIN platforms p ON p.id = tp.platform_id
                    WHERE tp.title_id = t.id AND p.name LIKE :q
                )
            )";
            $params[':q'] = '%' . $q . '%';
        }

        if ($type !== '' && in_array($type, ['film','serial'], true)) {
            $where[] = "t.type = :type";
            $params[':type'] = $type;
        }

        if ($year > 0) {
            $where[] = "t.year = :year";
            $params[':year'] = $year;
        }

        if ($category !== '') {
            $where[] = "EXISTS (
                SELECT 1 FROM title_category tc
                JOIN categories c ON c.id = tc.category_id
                WHERE tc.title_id = t.id AND c.name = :category
            )";
            $params[':category'] = $category;
        }

        if ($platform !== '') {
            $where[] = "EXISTS (
                SELECT 1 FROM title_platform tp
                JOIN platforms p ON p.id = tp.platform_id
                WHERE tp.title_id = t.id AND p.name = :platform
            )";
            $params[':platform'] = $platform;
        }

        $orderBy = "t.year DESC, t.name ASC";
        switch ($sort) {
            case 'oldest':   $orderBy = "t.year ASC, t.name ASC"; break;
            case 'name_asc': $orderBy = "t.name ASC, t.year DESC"; break;
            case 'name_desc':$orderBy = "t.name DESC, t.year DESC"; break;
            case 'year_asc': $orderBy = "t.year ASC, t.name ASC"; break;
            case 'year_desc':$orderBy = "t.year DESC, t.name ASC"; break;
            case 'newest':
            default:         $orderBy = "t.year DESC, t.name ASC"; break;
        }

        // KM3: dołączamy agregację ocen (łapki) do listy
        $sql = "SELECT t.*,
                    COALESCE(r.up_count, 0) AS up_count,
                    COALESCE(r.down_count, 0) AS down_count
                FROM titles t
                LEFT JOIN (
                    SELECT title_id,
                        SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) AS up_count,
                        SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) AS down_count
                    FROM ratings
                    GROUP BY title_id
                ) r ON r.title_id = t.id";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY " . $orderBy;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r['id'];
            $out[] = new Title(
                $id,
                $r['name'],
                $r['type'],
                (int)$r['year'],
                $r['description'],
                $r['poster'],
                $this->catRepo->namesForTitle($id),
                $this->platRepo->namesForTitle($id),
                (int)($r['up_count'] ?? 0),
                (int)($r['down_count'] ?? 0)
            );
        }
        return $out;
    }

    public function years(): array
    {
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT DISTINCT year FROM titles ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows ?: []);
    }

    public function suggest(string $q, int $limit = 8): array
    {
        $q = trim($q);
        if ($q === '') return [];

        $pdo = DB::conn();
        $limit = max(1, min(20, (int)$limit));

        $sql = "SELECT id, name, type, year
                FROM titles
                WHERE name LIKE :pfx OR name LIKE :any
                ORDER BY CASE WHEN name LIKE :pfx THEN 0 ELSE 1 END, year DESC, name ASC
                LIMIT " . $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pfx' => $q . '%',
            ':any' => '%' . $q . '%',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function all(): array
    {
        $pdo = DB::conn();
        $rows = $pdo->query("SELECT * FROM titles ORDER BY year DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $id = (int)$r['id'];
            $out[] = new Title(
                $id,
                $r['name'],
                $r['type'],
                (int)$r['year'],
                $r['description'],
                $r['poster'],
                $this->catRepo->namesForTitle($id),
                $this->platRepo->namesForTitle($id)
            );
        }
        return $out;
    }

    public function find(int $id): ?Title
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT t.*,
            COALESCE(r.up_count, 0) AS up_count,
            COALESCE(r.down_count, 0) AS down_count
            FROM titles t
            LEFT JOIN (
                SELECT title_id,
                    SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) AS up_count,
                    SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) AS down_count
                FROM ratings
                GROUP BY title_id
            ) r ON r.title_id = t.id
            WHERE t.id=:id");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;

        return new Title(
            (int)$r['id'],
            $r['name'],
            $r['type'],
            (int)$r['year'],
            $r['description'],
            $r['poster'],
            $this->catRepo->namesForTitle((int)$r['id']),
            $this->platRepo->namesForTitle((int)$r['id']),
            (int)($r['up_count'] ?? 0),
            (int)($r['down_count'] ?? 0)
        );
    }

    // --- KM3: oceny (łapka w górę / w dół) ---
    public function exists(int $id): bool
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT 1 FROM titles WHERE id=:id");
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    public function rate(int $titleId, string $clientId, int $value): void
    {
        $pdo = DB::conn();
        // Upsert: jeśli użytkownik już głosował, aktualizujemy
        $sql = "INSERT INTO ratings(title_id, client_id, value, updated_at)
                VALUES(:tid, :cid, :val, datetime('now'))
                ON CONFLICT(title_id, client_id)
                DO UPDATE SET value=excluded.value, updated_at=datetime('now')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tid' => $titleId,
            ':cid' => $clientId,
            ':val' => $value
        ]);
    }

    public function rateMod(int $titleId, int $amount, int $value): void
    {
        $pdo = DB::conn();

        $q = $pdo->prepare("
        SELECT MAX(CAST(SUBSTR(client_id, 11) AS INTEGER)) AS mx
        FROM ratings
        WHERE title_id = :tid AND client_id LIKE 'admin_mod_%'
    ");
        $q->execute([':tid' => $titleId]);
        $start = ((int)($q->fetchColumn() ?: 0)) + 1;

        $sql = "INSERT INTO ratings(title_id, client_id, value, updated_at)
            VALUES(:tid, :cid, :val, datetime('now'))";
        $stmt = $pdo->prepare($sql);

        for ($i = 0; $i < $amount; $i++) {
            $clientId = 'admin_mod_' . ($start + $i);

            $stmt->execute([
                ':tid' => $titleId,
                ':cid' => $clientId,
                ':val' => $value
            ]);
        }
    }

    public function listModVotes(int $titleId, int $limit = 200): array
    {
        $pdo = DB::conn();

        $sql = "SELECT
              rowid AS id,
              client_id,
              value,
              updated_at
            FROM ratings
            WHERE title_id = :tid
            ORDER BY updated_at DESC, rowid DESC
            LIMIT :lim";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tid', $titleId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function deleteRatingByRowId(int $titleId, int $rowId): void
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("DELETE FROM ratings WHERE title_id = :tid AND rowid = :rid");
        $stmt->execute([
            ':tid' => $titleId,
            ':rid' => $rowId
        ]);
    }


    public function ratingStats(int $titleId): array
    {
        $pdo = DB::conn();
        $stmt = $pdo->prepare("SELECT
                SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) AS up,
                SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) AS down
            FROM ratings
            WHERE title_id = :id");
        $stmt->execute([':id' => $titleId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['up' => 0, 'down' => 0];
        $up = (int)($r['up'] ?? 0);
        $down = (int)($r['down'] ?? 0);
        $total = $up + $down;
        $upPct = $total > 0 ? (int)round(($up / $total) * 100) : 0;
        $downPct = $total > 0 ? 100 - $upPct : 0;
        return compact('up','down','total','upPct','downPct');
    }

    public function create(array $data): int
    {
        $pdo = DB::conn();
        $sql = "INSERT INTO titles(name,type,year,description,poster)
                VALUES(:name,:type,:year,:description,:poster)";
        $pdo->prepare($sql)->execute([
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':year' => (int)$data['year'],
            ':description' => $data['description'],
            ':poster' => $data['poster']
        ]);
        $id = (int)$pdo->lastInsertId();

        $this->catRepo->setForTitle($id, $data['categories']);
        $this->platRepo->setForTitle($id, $data['platforms']);

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $pdo = DB::conn();
        $sql = "UPDATE titles SET name=:name, type=:type, year=:year, description=:description, poster=:poster
                WHERE id=:id";
        $pdo->prepare($sql)->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':year' => (int)$data['year'],
            ':description' => $data['description'],
            ':poster' => $data['poster']
        ]);

        $this->catRepo->setForTitle($id, $data['categories']);
        $this->platRepo->setForTitle($id, $data['platforms']);
    }

    public function delete(int $id): void
    {
        $pdo = DB::conn();
        $pdo->prepare("DELETE FROM titles WHERE id=:id")->execute([':id' => $id]);
    }
}
