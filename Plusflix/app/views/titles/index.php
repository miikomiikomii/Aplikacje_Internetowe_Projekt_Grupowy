<h2 class="pageTitle">Katalog</h2>
<div class="grid">
<?php foreach ($titles as $t): ?>
    <a class="card" href="<?= url('titles','show',['id'=>$t->id]) ?>">
        <div class="posterWrap">
            <img class="poster" src="public/posters/<?= h($t->poster) ?>" alt="poster">
        </div>
        <div class="meta">
            <div class="titleRow">
                <div class="name"><?= h($t->name) ?></div>
                <div class="badge"><?= h($t->type) ?></div>
            </div>
            <div class="sub"><?= h((string)$t->year) ?></div>
            <div class="chips">
                <?php foreach (array_slice($t->categories, 0, 3) as $c): ?>
                    <span class="chip"><?= h($c) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </a>
<?php endforeach; ?>
</div>
