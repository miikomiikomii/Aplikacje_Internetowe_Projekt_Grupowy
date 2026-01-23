<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PLUSFLIX • KM3</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= url('titles','index') ?>">PLUSFLIX</a>
    <nav class="nav">
        <?php if (isAdmin()): ?>
            <a href="<?= url('titles','create') ?>">Dodaj tytuł</a>
            <a href="<?= url('admin','dashboard') ?>">Panel admina</a>
            <a href="<?= url('admin','logout') ?>">Wyloguj</a>
        <?php else: ?>
            <a href="<?= url('admin','login') ?>">Admin</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
