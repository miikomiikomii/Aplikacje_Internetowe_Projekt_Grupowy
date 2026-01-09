<div class="detail">
    <div class="detailPoster">
        <img src="public/posters/<?= h($title->poster) ?>" alt="poster">
    </div>
    <div class="detailInfo">
        <h2><?= h($title->name) ?></h2>
        <div class="detailLine">
            <span class="badge"><?= h($title->type) ?></span>
            <span class="muted"><?= h((string)$title->year) ?></span>
        </div>
        <p class="desc"><?= h($title->description) ?></p>
        <div class="block">
            <div class="blockTitle">Kategorie</div>
            <div class="chips">
                <?php foreach ($title->categories as $c): ?>
                    <span class="chip"><?= h($c) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="block">
            <div class="blockTitle">Platformy</div>
            <div class="chips">
                <?php foreach ($title->platforms as $p): ?>
                    <span class="chip"><?= h($p) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="actions">
        <a class="btn" href="<?= url('titles','edit',['id'=>$title->id]) ?>">Edytuj</a>
        <form method="post" action="<?= url('titles','delete',['id'=>$title->id]) ?>" onsubmit="return confirm('Usunąć?')">
            <button class="btn danger" type="submit">Usuń</button>
        </form>
        <a class="btn ghost" href="<?= url('titles','index') ?>">Powrót</a>
    </div>
</div>
