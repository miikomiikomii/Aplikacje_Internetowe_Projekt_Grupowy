<?php

class AdminController extends Controller
{
    // Proste dane logowania (KM3) - można zmienić w tym pliku
    private const ADMIN_USER = 'admin';
    private const ADMIN_PASS = 'admin';

    public function loginAction(): void
    {
        // jeśli już zalogowany - dashboard
        if (isAdmin()) {
            $this->redirect(url('admin', 'dashboard'));
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $u = (string)($_POST['username'] ?? '');
            $p = (string)($_POST['password'] ?? '');
            if ($u === self::ADMIN_USER && $p === self::ADMIN_PASS) {
                $_SESSION['admin_logged_in'] = true;
                $this->redirect(url('admin', 'dashboard'));
            } else {
                $error = 'Nieprawidłowy login lub hasło';
            }
        }

        $this->render('admin/login', compact('error'));
    }

    public function logoutAction(): void
    {
        unset($_SESSION['admin_logged_in']);
        $this->redirect(url('titles', 'index'));
    }

    public function dashboardAction(): void
    {
        $this->requireAdmin();

        $pdo = DB::conn();
        $stats = [
            'titles' => (int)$pdo->query("SELECT COUNT(*) FROM titles")->fetchColumn(),
            'categories' => (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'platforms' => (int)$pdo->query("SELECT COUNT(*) FROM platforms")->fetchColumn(),
            'ratings' => (int)$pdo->query("SELECT COUNT(*) FROM ratings")->fetchColumn(),
        ];

        $sql = "SELECT
                  t.id,
                  t.name,
                  t.type,
                  t.year,
                COALESCE(REPLACE(GROUP_CONCAT(DISTINCT c.name), ',', ', '), '') AS categories,
                COALESCE(REPLACE(GROUP_CONCAT(DISTINCT p.name), ',', ', '), '') AS platforms
                FROM titles t
                LEFT JOIN title_category tc ON tc.title_id = t.id
                LEFT JOIN categories c ON c.id = tc.category_id
                LEFT JOIN title_platform tp ON tp.title_id = t.id
                LEFT JOIN platforms p ON p.id = tp.platform_id
                GROUP BY t.id
                ORDER BY t.id DESC
                LIMIT 8";

        $recent = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/dashboard', compact('stats','recent'));
    }

    public function moderateAction(): void
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $repo = new TitlesRepository();

        $title = $repo->find($id);
        if (!$title) {
            $this->notFound('Tytuł nie istnieje');
        }

        $votes = $repo->listModVotes($id, 200);

        $this->render('admin/moderate', compact('title','votes'));
    }

    public function deleteVoteAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->methodNotAllowed('Użyj POST');
        }

        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $this->badRequest('Niepoprawny JSON');
        }

        $titleId = (int)($data['title_id'] ?? 0);
        $voteId  = (int)($data['vote_id'] ?? 0);

        if ($titleId <= 0 || $voteId <= 0) {
            $this->badRequest('Brak wymaganych danych');
        }

        $repo = new TitlesRepository();
        $repo->deleteRatingByRowId($titleId, $voteId);

        $stats = $repo->ratingStats($titleId);
        $votes = $repo->listModVotes($titleId, 200);

        echo json_encode(['ok' => true, 'stats' => $stats, 'votes' => $votes]);
        exit;
    }

    public function resetAction(): void
    {
        $this->requireAdmin();
        DB::reset();
        $this->redirect(url('admin', 'dashboard'));
    }

    public function bulkRateAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->methodNotAllowed('Użyj metody POST');
        }

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->badRequest('Niepoprawny JSON');
        }

        $titleId = (int)($data['title_id'] ?? 0);
        $value   = (int)($data['value'] ?? 0);
        $amount  = (int)($data['amount'] ?? 0);

        if ($titleId <= 0) {
            $this->badRequest('Brak title_id');
        }
        if (!in_array($value, [1, -1], true)) {
            $this->badRequest('Niepoprawna wartość głosu');
        }
        if ($amount <= 0) {
            $this->badRequest('amount musi być > 0');
        }

        $amount = min($amount, 5000);

        $repo = new TitlesRepository();

        $repo->rateMod($titleId, $amount, $value);

        $stats = $repo->ratingStats($titleId);
        $votes = $repo->listModVotes($titleId, 200);

        echo json_encode([
            'ok' => true,
            'stats' => $stats,
            'votes' => $votes
        ]);
        exit;
    }
}
