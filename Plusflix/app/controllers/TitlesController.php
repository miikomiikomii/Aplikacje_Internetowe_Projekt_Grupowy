<?php

class TitlesController extends Controller
{

    public function indexAction(): void
    {
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'type' => trim((string)($_GET['type'] ?? '')),
            'year' => (int)($_GET['year'] ?? 0),
            'category' => trim((string)($_GET['category'] ?? '')),
            'platform' => trim((string)($_GET['platform'] ?? '')),
            'sort' => trim((string)($_GET['sort'] ?? '')),
        ];

        if (!in_array($filters['type'], ['film','serial'], true)) {
            $filters['type'] = '';
        }

        // sort whitelist
        $allowedSort = ['newest','oldest','name_asc','name_desc','year_asc','year_desc'];
        if (!in_array($filters['sort'], $allowedSort, true)) {
            $filters['sort'] = 'newest';
        }

        $repo = new TitlesRepository();
        $titles = $repo->search($filters);

        $categories = (new CategoryRepository())->all();
        $platforms = (new PlatformRepository())->all();
        $years = $repo->years();

        $this->render('titles/index', compact('titles','filters','categories','platforms','years'));
    }

    public function autocompleteAction(): void
    {

        $q = trim((string)($_GET['q'] ?? ''));
        if (strlen($q) < 2) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([]);
            return;
        }

        $items = (new TitlesRepository())->suggest($q, 8);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    public function showAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $title = (new TitlesRepository())->find($id);
        if (!$title) { http_response_code(404); exit('Nie znaleziono'); }
        $this->render('titles/show', compact('title'));
    }

    public function createAction(): void
    {
        $this->render('titles/form', ['mode' => 'create', 'title' => null]);
    }

    public function storeAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $data = $this->readFormData();
        $id = (new TitlesRepository())->create($data);
        $this->redirect(url('titles', 'show', ['id' => $id]));
    }

    public function editAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $title = (new TitlesRepository())->find($id);
        if (!$title) { http_response_code(404); exit('Nie znaleziono'); }
        $this->render('titles/form', ['mode' => 'edit', 'title' => $title]);
    }

    public function updateAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->readFormData();
        (new TitlesRepository())->update($id, $data);
        $this->redirect(url('titles', 'show', ['id' => $id]));
    }

    public function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $id = (int)($_GET['id'] ?? 0);
        (new TitlesRepository())->delete($id);
        $this->redirect(url('titles', 'index'));
    }

    private function readFormData(): array
    {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? 'film');
        $year = (int)($_POST['year'] ?? 2000);
        $description = trim($_POST['description'] ?? '');
        $poster = trim($_POST['poster'] ?? 'placeholder.jpg');
        $categories = splitList($_POST['categories'] ?? '');
        $platforms = splitList($_POST['platforms'] ?? '');

        if ($name === '' || $description === '') {
            http_response_code(400);
            exit('Brak wymaganych pól');
        }
        if (!in_array($type, ['film','serial'], true)) {
            $type = 'film';
        }
        if ($year < 1900 || $year > 2100) {
            $year = 2000;
        }

        return compact('name', 'type', 'year', 'description', 'poster', 'categories', 'platforms');
    }

}