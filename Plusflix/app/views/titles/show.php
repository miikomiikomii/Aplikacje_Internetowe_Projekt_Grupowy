<div class="detail">
    <div class="detailPoster">
        <img src="public/posters/<?= h($title->getPoster()) ?>" alt="poster">
    </div>
    <div class="detailInfo">
        <h2><?= h($title->getName()) ?></h2>
        <div class="detailLine">
            <span class="badge"><?= h($title->getType()) ?></span>
            <span class="muted"><?= h((string)$title->getYear()) ?></span>
        </div>
        <p class="desc"><?= h($title->getDescription()) ?></p>
        <div class="block">
            <div class="blockTitle">Kategorie</div>
            <div class="chips">
                <?php foreach ($title->getCategories() as $c): ?>
                    <span class="chip"><?= h($c) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="block">
            <div class="blockTitle">Platformy</div>
            <div class="chips">
                <?php foreach ($title->getPlatforms() as $p): ?>
                    <span class="chip"><?= h($p) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="actions">
        <a class="btn" href="<?= url('titles','edit',['id'=>$title->getId()]) ?>">Edytuj</a>
        <form method="post" action="<?= url('titles','delete',['id'=>$title->getId()]) ?>" onsubmit="return confirm('Usunąć?')">
            <button class="btn danger" type="submit">Usuń</button>
        </form>
        <a class="btn ghost" href="<?= url('titles','index') ?>">Powrót</a>
    </div>
</div>
