<h2 class="pageTitle">Katalog</h2>

<?php
// KM2: wartości filtrów (bezpieczne domyślne)
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
    <span class="pipe">•</span>
    <button type="button" id="shareFavs" class="miniBtn" title="Skopiuj link z ulubionymi">Udostępnij ulubione</button>
    <button type="button" id="toggleFavOnly" class="miniBtn" title="Pokaż tylko ulubione">Pokaż ulubione</button>
</div>

<div class="grid">
    <?php if (empty($titles)): ?>
        <div class="emptyState">
            Brak wyników. Zmień kryteria wyszukiwania lub wyczyść filtry.
        </div>
    <?php endif; ?>

    <?php foreach ($titles as $t): ?>
        <a class="card" data-title-id="<?= (int)$t->getId() ?>" href="<?= url('titles','show',['id'=>$t->getId()]) ?>">
            <div class="posterWrap">
                <img class="poster" src="public/posters/<?= h($t->getPoster()) ?>" alt="poster">
                <button type="button" class="favBtn" data-id="<?= (int)$t->getId() ?>" aria-label="Ulubione">♡</button>
            </div>
            <div class="meta">
                <div class="titleRow">
                    <div class="name"><?= h($t->getName()) ?></div>
                    <div class="badge"><?= h($t->getType()) ?></div>
                </div>
                <div class="sub"><?= h((string)$t->getYear()) ?></div>
                <div class="chips">
                    <?php foreach (array_slice($t->getCategories(), 0, 3) as $c): ?>
                        <span class="chip"><?= h($c) ?></span>
                    <?php endforeach; ?>
                </div>

                <?php if ($t->getRatingTotal() > 0): ?>
                    <div class="ratingLine">
                        <div class="ratingBar" aria-label="Oceny">
                            <span class="up" style="width:<?= (int)$t->getRatingUpPct() ?>%"></span>
                        </div>
                        <div class="ratingPct">
                            👍 <?= (int)$t->getRatingUpPct() ?>% / 👎 <?= (int)$t->getRatingDownPct() ?>%
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ratingLine muted">Brak ocen</div>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<script>
  // KM3: ulubione w localStorage + stan w URL (fav=1,2,3)
    (function(){
    const KEY = 'pf_favs';

    function getFavs(){
        try { return JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch(e){ return []; }
    }

    function setFavs(ids){
        const uniq = Array.from(new Set(ids.map(x => parseInt(x,10)).filter(x => Number.isFinite(x) && x > 0)));
        localStorage.setItem(KEY, JSON.stringify(uniq));
        window.dispatchEvent(new Event('pf:favs'));
        syncUrl();
        paint();
    }

    function toggle(id){
        const ids = getFavs();
        const i = ids.indexOf(id);
        if (i >= 0) ids.splice(i,1);
        else ids.push(id);
        setFavs(ids);
    }

    function syncUrl(){
        const ids = getFavs();
        const u = new URL(window.location.href);
        if (ids.length > 0) u.searchParams.set('fav', ids.join(','));
        else u.searchParams.delete('fav');
        const shared = u.searchParams.get('U');
        if (shared) u.searchParams.delete('U');
        history.replaceState({}, '', u.toString());
    }

    function paint(){
        const ids = new Set(getFavs());
        document.querySelectorAll('.favBtn').forEach(btn => {
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const on = ids.has(id);
        btn.textContent = on ? '♥' : '♡';
        btn.classList.toggle('on', on);
        });
    }

    // obsługa kliknięć w serduszka
    document.addEventListener('click', (e) => {
        const btn = e.target && e.target.closest ? e.target.closest('.favBtn') : null;
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const id = parseInt(btn.getAttribute('data-id'), 10);
        if (Number.isFinite(id)) toggle(id);
    });

    // pokaż tylko ulubione
    const toggleBtn = document.getElementById('toggleFavOnly');
    let favOnly = false;
    function applyFavOnly(){
        const ids = new Set(getFavs());
        document.querySelectorAll('.card[data-title-id]').forEach(card => {
        const id = parseInt(card.getAttribute('data-title-id'), 10);
        const show = !favOnly || ids.has(id);
        card.style.display = show ? '' : 'none';
        });
        toggleBtn.textContent = favOnly ? 'Pokaż wszystkie' : 'Pokaż ulubione';
    }
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
        favOnly = !favOnly;
        applyFavOnly();
        });
    }
    window.addEventListener('pf:favs', applyFavOnly);

    function parseFavParam(){
        const u = new URL(window.location.href);
        const fav = u.searchParams.get('fav');
        if (!fav) return null;
        const ids = fav.split(',').map(x => parseInt(x, 10)).filter(x => Number.isFinite(x) && x > 0);
        const shared = u.searchParams.get('U');
        console.log(shared);
        let sh = false;
        if (shared) sh = true;
        return [Array.from(new Set(ids)), sh];
    }

    // import favs z URL (jeśli przesłane)
    const fromUrl = parseFavParam();
    if (fromUrl && fromUrl[0].length > 0) {
        setFavs(fromUrl[0]);
        if(fromUrl[1] === true){
            favOnly = !favOnly;
            applyFavOnly();
            syncUrl()
        }
    } else {
        syncUrl();
        paint();
    }

    // udostępnianie linku z fav param
    const shareBtn = document.getElementById('shareFavs');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
        syncUrl();
        try {
            await navigator.clipboard.writeText(window.location.href+"&U=1");
            shareBtn.textContent = 'Skopiowano!';
            setTimeout(() => shareBtn.textContent = 'Udostępnij ulubione', 1200);
        } catch(e) {
            alert('Nie udało się skopiować linku. Skopiuj ręcznie z paska adresu.');
        }
        });
    }
    })();
</script>

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
                    if (input.value.trim() !== q) return;
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
