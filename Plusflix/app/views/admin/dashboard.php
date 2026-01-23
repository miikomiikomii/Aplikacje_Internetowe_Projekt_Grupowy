<h2 class="pageTitle">Dashboard</h2>

<div class="dashGrid">
  <div class="dashCard">
    <div class="dashLabel">Tytuły</div>
    <div class="dashValue"><?= (int)($stats['titles'] ?? 0) ?></div>
  </div>
  <div class="dashCard">
    <div class="dashLabel">Kategorie</div>
    <div class="dashValue"><?= (int)($stats['categories'] ?? 0) ?></div>
  </div>
  <div class="dashCard">
    <div class="dashLabel">Platformy</div>
    <div class="dashValue"><?= (int)($stats['platforms'] ?? 0) ?></div>
  </div>
  <div class="dashCard">
    <div class="dashLabel">Oceny (głosy)</div>
    <div class="dashValue"><?= (int)($stats['ratings'] ?? 0) ?></div>
  </div>
</div>

<div class="cardLike">
  <div class="cardLikeHeader">
    <h3>Operacje</h3>
    <div class="btnRow">
      <a class="btn" href="<?= url('titles','create') ?>">Dodaj tytuł</a>
      <a class="btn ghost" href="<?= url('titles','index') ?>">Przejdź do katalogu</a>
      <a class="btn danger" href="<?= url('admin','reset') ?>" onclick="return confirm('Na pewno zresetować bazę?')">Reset bazy</a>
    </div>
  </div>

  <h3 style="margin-top:18px;">Ostatnio dodane</h3>
  <?php if (empty($recent)): ?>
    <div class="muted">Brak danych</div>
  <?php else: ?>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Typ</th>
            <th>Rok</th>
            <th>Kategorie</th>
            <th>Platformy</th>
            <th></th>
        </tr>
        </thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= h($r['name']) ?></td>
            <td><?= h($r['type']) ?></td>
            <td><?= (int)$r['year'] ?></td>
              <td><?= h($r['categories'] ?? '') ?></td>
              <td><?= h($r['platforms'] ?? '') ?></td>
            <td>
              <a class="btnLink" href="<?= url('admin','moderate',['id'=>(int)$r['id']]) ?>">Edytuj oceny</a>
              <span class="muted">•</span>
              <a class="btnLink" href="<?= url('titles','edit',['id'=>(int)$r['id']]) ?>">Edytuj</a>
              <span class="muted">•</span>
              <a class="btnLink" href="<?= url('titles','show',['id'=>(int)$r['id']]) ?>">Podgląd</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
