<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/app/core/helpers.php';

spl_autoload_register(function (string $class): void {
    foreach (['app/controllers','app/models','app/repositories','app/core'] as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (file_exists($file)) { require $file; return; }
    }
});

// nawigacja przy pomocy url gdzie:
// c <- wskazany Controller
// a <- wskazana akcja(funkcja) z Controller-a
$c = $_GET['c'] ?? 'titles';
$a = $_GET['a'] ?? 'index';

$controllerClass = ucfirst($c) . 'Controller'; // np. TitlesController
$actionMethod = $a . 'Action'; // np. indexAction

if (!class_exists($controllerClass) || !method_exists($controllerClass, $actionMethod)) {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}

try {
    DB::conn();
    $controller = new $controllerClass();
    $controller->$actionMethod();
} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre>Błąd: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
}
