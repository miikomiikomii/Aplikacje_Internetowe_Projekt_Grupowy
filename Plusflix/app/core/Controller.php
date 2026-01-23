<?php

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    // KM3: prosta ochrona akcji admina
    protected function requireAdmin(): void
    {
        if (!isAdmin()) {
            $this->redirect(url('admin', 'login'));
        }
    }

    // errory
    protected function abort(int $code, ?string $message = null): void
    {
        http_response_code($code);

        $errorMessage = $message ?? match ($code) {
            400 => 'Nieprawidłowe żądanie',
            403 => 'Brak dostępu',
            404 => 'Nie znaleziono',
            405 => 'Niedozwolona metoda',
            default => 'Wystąpił błąd'
        };

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/errors/' . $code . '.php';
        require __DIR__ . '/../views/layout/footer.php';

        exit;
    }

    protected function badRequest(?string $msg = null): void
    {
        $this->abort(400, $msg);
    }

    protected function methodNotAllowed(?string $msg = null): void
    {
        $this->abort(405, $msg);
    }

    protected function notFound(?string $msg = null): void
    {
        $this->abort(404, $msg);
    }

}
