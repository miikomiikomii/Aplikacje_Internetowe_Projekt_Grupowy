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

        <div class="block">
            <div class="blockTitle">Oceny</div>
            <div class="rateBox" data-title-id="<?= (int)$title->getId() ?>">
                <button type="button" class="rateBtn up" data-val="1" aria-label="Łapka w górę">👍</button>
                <button type="button" class="rateBtn down" data-val="-1" aria-label="Łapka w dół">👎</button>
                <div class="rateStats">
                    <span id="rateText">
                        <?php if ($title->getRatingTotal() > 0): ?>
                            👍 <?= (int)$title->getRatingUpPct() ?>% / 👎 <?= (int)$title->getRatingDownPct() ?>%
                            <span class="muted">(<?= (int)$title->getRatingTotal() ?> głosów)</span>
                        <?php else: ?>
                            <span class="muted">Brak ocen</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        <button type="button" id="favOne" class="btn favBtn" aria-label="Ulubione">Ulubione</button>

        <?php if (isAdmin()): ?>
            <a class="btn" href="<?= url('titles','edit',['id'=>$title->getId()]) ?>">Edytuj</a>
            <form method="post" action="<?= url('titles','delete',['id'=>$title->getId()]) ?>" onsubmit="return confirm('Usunąć?')">
                <button class="btn danger" type="submit">Usuń</button>
            </form>
        <?php endif; ?>
        <a class="btn ghost" href="<?= url('titles','index') ?>">Powrót</a>
    </div>
</div>

<script>
    // KM3: ulubione + oceny dla konkretnego tytułu
    (function(){
    const titleId = <?= (int)$title->getId() ?>;
    const FAV_KEY = 'pf_favs';
    const CID_KEY = 'pf_client_id';

    function getFavs(){ try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]') || []; } catch(e){ return []; } }
    function setFavs(ids){ localStorage.setItem(FAV_KEY, JSON.stringify(ids)); window.dispatchEvent(new Event('pf:favs')); }

    function paintFav(){
        const btn = document.getElementById('favOne');
        if (!btn) return;
        const ids = new Set(getFavs());
        const on = ids.has(titleId);
        btn.textContent = (on ? '♥' : '♡') + ' Ulubione';
        btn.classList.toggle('on', on);
    }

    const favBtn = document.getElementById('favOne');
    if (favBtn) {
        favBtn.addEventListener('click', () => {
        const ids = getFavs();
        const i = ids.indexOf(titleId);
        if (i >= 0) ids.splice(i,1); else ids.push(titleId);
        setFavs(ids);

        const u = new URL(window.location.href);
        u.searchParams.set('fav', ids.join(','));
        history.replaceState({}, '', u.toString());
        paintFav();
        });
        paintFav();
    }

    function getClientId(){
        let cid = localStorage.getItem(CID_KEY);
        if (!cid) {
            cid = (crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now().toString(36) + Math.random().toString(36).slice(2));
            localStorage.setItem(CID_KEY, cid);
        }
        return cid;
    }

    function getVote(){
        try { return parseInt(localStorage.getItem('pf_vote_' + titleId) || '0', 10) || 0; } catch(e){ return 0; }
    }
    function setVote(v){ localStorage.setItem('pf_vote_' + titleId, String(v)); }

    function paintVote(){
        const v = getVote();
        document.querySelectorAll('.rateBtn').forEach(b => {
        const val = parseInt(b.getAttribute('data-val'), 10);
        b.classList.toggle('on', val === v);
        });
    }

    async function sendVote(v){
        const res = await fetch("<?= url('titles','rate') ?>", {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
        body: JSON.stringify({ title_id: titleId, value: v, client_id: getClientId() })
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error((data && data.error) ? data.error : 'Błąd');
        return data.stats;
    }

    function renderStats(stats){
        const el = document.getElementById('rateText');
        if (!el) return;
        if (!stats || stats.total <= 0) {
        el.innerHTML = '<span class="muted">Brak ocen</span>';
        return;
        }
        el.innerHTML = `👍 ${stats.upPct}% / 👎 ${stats.downPct}% <span class="muted">(${stats.total} głosów)</span>`;
    }

    document.querySelectorAll('.rateBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
        const v = parseInt(btn.getAttribute('data-val'), 10);
        if (![1,-1].includes(v)) return;
        try {
            setVote(v);
            paintVote();
            const stats = await sendVote(v);
            renderStats(stats);
        } catch(e) {
            alert('Nie udało się zapisać oceny: ' + e.message);
        }
        });
    });
    paintVote();
})();
</script>
