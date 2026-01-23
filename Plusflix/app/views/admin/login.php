<h2 class="pageTitle">Panel administratora</h2>

<div class="cardLike">
  <h3>Zaloguj się</h3>
  <?php if (!empty($error)): ?>
    <div class="alert"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" class="form">
    <div class="field">
      <label>Login</label>
      <input name="username" type="text" required>
    </div>
    <div class="field">
      <label>Hasło</label>
      <input name="password" type="password" required>
    </div>
    <div class="btnRow">
      <button type="submit" class="btn">Zaloguj</button>
      <a class="btnLink" href="<?= url('titles','index') ?>">Powrót</a>
    </div>
  </form>

  <p class="muted" style="margin-top:10px;">Domyślne dane: <strong>admin / admin</strong> (możesz zmienić w <code>app/controllers/AdminController.php</code>).</p>
</div>
