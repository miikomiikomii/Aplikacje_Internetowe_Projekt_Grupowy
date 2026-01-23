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

                <input
                        id="bulkAmount"
                        name="amount"
                        type="number"
                        min="1"
                        value="1"
                        required
                        aria-label="Liczba głosów do dodania"
                        title="Liczba głosów do dodania"
                >

                <button type="button" class="rateBtn up" data-val="1" aria-label="Dodaj łapki w górę">👍</button>
                <button type="button" class="rateBtn down" data-val="-1" aria-label="Dodaj łapki w dół">👎</button>

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

            <div class="block" style="margin-top:12px">
                <div class="blockTitle">Oddane głosy</div>

                <div class="cardLike" style="padding:12px">
                    <div id="votesEmpty" class="muted" <?= empty($votes) ? '' : 'style="display:none"' ?>>
                        Brak dodanych głosów przez moderatora.
                    </div>

                    <div class="votesScroll" id="votesScroll" <?= empty($votes) ? 'style="display:none"' : '' ?>>
                        <table class="table adminVotesTable" id="adminVotesTable">
                            <thead>
                            <tr>
                                <th style="width:70px">ID</th>
                                <th style="width:300px">client_id</th>
                                <th style="width:80px">value</th>
                                <th>updated_at</th>
                                <th style="width:90px"></th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($votes as $v): ?>
                                <tr data-vote-id="<?= (int)$v['id'] ?>">
                                    <td class="muted"><?= (int)$v['id'] ?></td>
                                    <td><?= h($v['client_id']) ?></td>
                                    <td><?= ((int)$v['value'] === 1) ? '👍' : '👎' ?></td>
                                    <td class="muted"><?= h($v['updated_at']) ?></td>
                                    <td style="text-align:right">
                                        <button type="button" class="miniBtn delVoteBtn">Usuń</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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

<script>
    const titleId = <?= (int)$title->getId() ?>;
    const rateTextEl = document.getElementById('rateText');

    function renderStats(stats){
        if (!rateTextEl) return;
        if (!stats || !stats.total || stats.total <= 0) {
            rateTextEl.innerHTML = '<span class="muted">Brak ocen</span>';
            return;
        }
        rateTextEl.innerHTML =
            `👍 ${stats.upPct}% / 👎 ${stats.downPct}% <span class="muted">(${stats.total} głosów)</span>`;
    }

    function renderVotes(votes){
        const empty = document.getElementById('votesEmpty');
        const scroll = document.getElementById('votesScroll');
        const table = document.getElementById('adminVotesTable');

        if (!table || !scroll) return;

        if (!votes || votes.length === 0){
            if (empty) empty.style.display = '';
            scroll.style.display = 'none';
            return;
        }

        if (empty) empty.style.display = 'none';
        scroll.style.display = '';

        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';

        votes.forEach(v => {
            const tr = document.createElement('tr');
            tr.dataset.voteId = v.id;
            const icon = (parseInt(v.value,10) === 1) ? '👍' : '👎';
            tr.innerHTML = `
      <td class="muted">${v.id}</td>
      <td>${escapeHtml(v.client_id)}</td>
      <td>${icon}</td>
      <td class="muted">${escapeHtml(v.updated_at)}</td>
      <td style="text-align:right"><button type="button" class="miniBtn delVoteBtn">Usuń</button></td>
    `;
            tbody.appendChild(tr);
        });
    }

    (function(){

        const amountInput = document.getElementById('bulkAmount');
        const rateTextEl  = document.getElementById('rateText');

        function getAmount(){
            const a = amountInput ? parseInt(amountInput.value || '1', 10) : 1;
            return Number.isFinite(a) ? a : 0;
        }


        async function sendBulkVote(value, amount){
            const res = await fetch("<?= url('admin','bulkRate') ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    title_id: titleId,
                    value,
                    amount
                })
            });

            const data = await res.json().catch(() => null);

            if (!res.ok || !data || !data.ok) {
                const msg = (data && data.error) ? data.error : 'Błąd';
                throw new Error(msg);
            }

            return data;
        }

        document.querySelectorAll('.rateBtn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const value = parseInt(btn.getAttribute('data-val'), 10);
                if (![1, -1].includes(value)) return;

                const amount = getAmount();
                if (amount < 1) {
                    alert('Podaj poprawną liczbę głosów (min. 1).');
                    return;
                }

                try {
                    btn.disabled = true;
                    const data = await sendBulkVote(value, amount);
                    renderStats(data.stats);
                    renderVotes(data.votes);
                } catch (e) {
                    alert('Nie udało się dodać głosów: ' + e.message);
                } finally {
                    btn.disabled = false;
                }
            });
        });
    })();

    function escapeHtml(s){
        return String(s ?? '')
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }

    async function deleteVote(voteId){
        const res = await fetch("<?= url('admin','deleteVote') ?>", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
            body: JSON.stringify({ title_id: titleId, vote_id: voteId })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.ok) {
            throw new Error((data && data.error) ? data.error : 'Błąd');
        }

        return data;
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.delVoteBtn');
        if (!btn) return;

        const tr = btn.closest('tr');
        const voteId = tr ? parseInt(tr.dataset.voteId || '0', 10) : 0;
        if (!voteId) return;

        if (!confirm('Usunąć ten głos?')) return;

        try {
            btn.disabled = true;
            const data = await deleteVote(voteId);
            renderStats(data.stats);
            renderVotes(data.votes);
        } catch (err) {
            alert('Nie udało się usunąć: ' + err.message);
        } finally {
            btn.disabled = false;
        }
    });

</script>
