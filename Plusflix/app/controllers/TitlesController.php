<?php

class TitlesController extends Controller
{

    public function indexAction(): void
    {
        $titles = (new TitleRepository())->all();
        $this->render('titles/index', compact('titles'));
    }

    public function showAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $title = (new TitleRepository())->find($id);
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
        $id = (new TitleRepository())->create($data);
        $this->redirect(url('titles', 'show', ['id' => $id]));
    }

    public function editAction(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $title = (new TitleRepository())->find($id);
        if (!$title) { http_response_code(404); exit('Nie znaleziono'); }
        $this->render('titles/form', ['mode' => 'edit', 'title' => $title]);
    }

    public function updateAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->readFormData();
        (new TitleRepository())->update($id, $data);
        $this->redirect(url('titles', 'show', ['id' => $id]));
    }

    public function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        $id = (int)($_GET['id'] ?? 0);
        (new TitleRepository())->delete($id);
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