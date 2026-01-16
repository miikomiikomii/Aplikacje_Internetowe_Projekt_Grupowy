<h2 class="pageTitle">Katalog</h2>

<?php
$filters = $filters ?? ['q'=>'','type'=>'','year'=>0,'category'=>'','sort'=>'newest'];
$categories = $categories ?? [];
$years = $years ?? [];
?>

<form class="filtersForm" method="get" action="index.php">
    <input type="hidden" name="c" value="titles">
    <input type="hidden" name="a" value="index">

    <div class="filtersGrid">
        <div class="field autocomplete">
            <label for="searchInput">Szukaj</label>
            <input id="searchInput" name="q" type="text" value="<?= h((string)$filters['q']) ?>" placeholder="np. Inception, sci-fi..." autocomplete="off">
            <div id="acBox" class="acBox" hidden></div>
        </div>

        <div class="field">
            <label for="typeSelect">Typ</label>
            <select id="typeSelect" name="type">
                <option value="" <?= $filters['type']==='' ? 'selected' : '' ?>>Wszystkie</option>
                <option value="film" <?= $filters['type']==='film' ? 'selected' : '' ?>>Film</option>
                <option value="serial" <?= $filters['type']==='serial' ? 'selected' : '' ?>>Serial</option>
            </select>
        </div>

        <div class="field">
            <label for="yearSelect">Rok</label>
            <select id="yearSelect" name="year">
                <option value="0" <?= (int)$filters['year']===0 ? 'selected' : '' ?>>Wszystkie</option>
                <?php foreach ($years as $y): ?>
                    <option value="<?= (int)$y ?>" <?= (int)$filters['year']===(int)$y ? 'selected' : '' ?>><?= (int)$y ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="catSelect">Kategoria</label>
            <select id="catSelect" name="category">
                <option value="" <?= $filters['category']==='' ? 'selected' : '' ?>>Wszystkie</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= h($cat->name) ?>" <?= $filters['category']===$cat->name ? 'selected' : '' ?>><?= h($cat->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="platSelect">Platforma</label>
            <select id="platSelect" name="platform">
                <option value="" <?= ($filters['platform'] ?? '')==='' ? 'selected' : '' ?>>Wszystkie</option>
                <?php foreach ($platforms as $plat): ?>
                    <option value="<?= h($plat->name) ?>" <?= (($filters['platform'] ?? '')===$plat->name) ? 'selected' : '' ?>><?= h($plat->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <div class="field">
            <label for="sortSelect">Sortuj</label>
            <select id="sortSelect" name="sort">
                <option value="newest" <?= $filters['sort']==='newest' ? 'selected' : '' ?>>Najnowsze</option>
                <option value="oldest" <?= $filters['sort']==='oldest' ? 'selected' : '' ?>>Najstarsze</option>
                <option value="name_asc" <?= $filters['sort']==='name_asc' ? 'selected' : '' ?>>Nazwa A→Z</option>
                <option value="name_desc" <?= $filters['sort']==='name_desc' ? 'selected' : '' ?>>Nazwa Z→A</option>
                <option value="year_asc" <?= $filters['sort']==='year_asc' ? 'selected' : '' ?>>Rok rosnąco</option>
                <option value="year_desc" <?= $filters['sort']==='year_desc' ? 'selected' : '' ?>>Rok malejąco</option>
            </select>
        </div>

        <div class="field buttons">
            <label>&nbsp;</label>
            <div class="btnRow">
                <button type="submit">Zastosuj</button>
                <a class="btnLink" href="<?= url('titles','index') ?>">Wyczyść</a>
            </div>
        </div>
    </div>
</form>

<div class="resultsMeta">
    Wyniki: <strong><?= count($titles) ?></strong>
</div>

<div class="grid">
    <?php if (empty($titles)): ?>
        <div class="emptyState">
            Brak wyników. Zmień kryteria wyszukiwania lub wyczyść filtry.
        </div>
    <?php endif; ?>

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

<script>
    (function(){
        const input = document.getElementById('searchInput');
        const box = document.getElementById('acBox');
        if (!input || !box) return;

        let t = null;
        let lastQ = '';

        function hide(){ box.hidden = true; box.innerHTML = ''; }
        function show(items){
            if (!items || items.length === 0){ hide(); return; }
            box.hidden = false;
            box.innerHTML = items.map(it => {
                const title = escapeHtml(it.name);
                const meta = escapeHtml((it.type || '') + ' • ' + (it.year || ''));
                const href = "<?= url('titles','show',['id'=>'__ID__']) ?>".replace('__ID__', it.id);
                return `<a class="acItem" href="${href}">
                        <div class="acTitle">${title}</div>
                        <div class="acMeta">${meta}</div>
                    </a>`;
            }).join('');
        }

        function escapeHtml(s){
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
        }

        async function fetchSuggest(q){
            const url = "<?= url('titles','autocomplete') ?>" + "&q=" + encodeURIComponent(q);
            const res = await fetch(url, {headers: {'Accept':'application/json'}});
            if (!res.ok) return [];
            return await res.json();
        }

        input.addEventListener('input', () => {
            const q = input.value.trim();
            lastQ = q;
            if (t) clearTimeout(t);
            if (q.length < 2){ hide(); return; }
            t = setTimeout(async () => {
                try {
                    const items = await fetchSuggest(q);
                    if (input.value.trim() !== q) return; // outdated
                    show(items);
                } catch(e){ hide(); }
            }, 180);
        });

        input.addEventListener('focus', () => {
            const q = input.value.trim();
            if (q.length >= 2 && box.innerHTML.trim() !== '') box.hidden = false;
        });

        document.addEventListener('click', (e) => {
            if (e.target === input) return;
            if (box.contains(e.target)) return;
            hide();
        });
    })();
</script>
