<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PLUSFLIX • KM1</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= url('titles','index') ?>">PLUSFLIX</a>
  <nav class="nav">
    <a href="<?= url('titles','create') ?>">Dodaj tytuł</a>
    <a href="<?= url('setup','reset') ?>" onclick="return confirm('Zresetować bazę?')">Reset DB</a>
  </nav>
</header>
<main class="container">
