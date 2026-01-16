<?php
$editing = ($mode === 'edit');
$catsDefault = $editing ? implode(', ', $title->getCategories()) : '';
$platsDefault = $editing ? implode(', ', $title->getPlatforms()) : '';
?>
<h2 class="pageTitle"><?= $editing ? 'Edycja tytułu' : 'Dodawanie tytułu' ?></h2>

<form class="form" method="post" action="<?= $editing ? url('titles','update',['id'=>$title->getId()]) : url('titles','store') ?>">
    <label>Nazwa
        <input name="name" value="<?= $editing ? h($title->getName()) : '' ?>" required>
    </label>

    <div class="row2">
        <label>Typ
            <select name="type">
                <option value="film" <?= $editing && $title->getType()==='film' ? 'selected' : '' ?>>film</option>
                <option value="serial" <?= $editing && $title->getType()==='serial' ? 'selected' : '' ?>>serial</option>
            </select>
        </label>

        <label>Rok
            <input name="year" type="number" min="1900" max="2100" value="<?= $editing ? h((string)$title->getYear()) : '2020' ?>" required>
        </label>
    </div>

    <label>Opis
        <textarea name="description" rows="4" required><?= $editing ? h($title->getDescription()) : '' ?></textarea>
    </label>

    <label>Plakat (plik w public/posters)
        <input name="poster" value="<?= $editing ? h($title->getPoster()) : 'placeholder.jpg' ?>" required>
    </label>

    <label>Kategorie (oddzielone przecinkami)
        <input name="categories" value="<?= h($catsDefault) ?>" placeholder="np. Sci-Fi, Thriller">
    </label>

    <label>Platformy (oddzielone przecinkami)
        <input name="platforms" value="<?= h($platsDefault) ?>" placeholder="np. Netflix, Disney+">
    </label>

    <div class="actions">
        <button class="btn" type="submit"><?= $editing ? 'Zapisz' : 'Utwórz' ?></button>
        <a class="btn ghost" href="<?= url('titles','index') ?>">Anuluj</a>
    </div>
</form>
