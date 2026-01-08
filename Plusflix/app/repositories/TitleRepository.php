<?php
class TitleRepository
{
    private CategoryRepository $catRepo;
    private PlatformRepository $platRepo;

    public function __construct()
    {
        $this->catRepo = new CategoryRepository();
        $this->platRepo = new PlatformRepository();
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
        $stmt = $pdo->prepare("SELECT * FROM titles WHERE id=:id");
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
            $this->platRepo->namesForTitle((int)$r['id'])
        );
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
