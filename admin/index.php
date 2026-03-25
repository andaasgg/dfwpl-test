<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$active_page = '';
$msg = '';
$error = '';

// ── Handle first-time setup ────────────────────────────────
if (!has_admin_password()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup') {
        $pw  = $_POST['password']  ?? '';
        $pw2 = $_POST['password2'] ?? '';
        if (strlen($pw) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($pw !== $pw2) {
            $error = 'Passwords do not match.';
        } else {
            $hash   = password_hash($pw, PASSWORD_DEFAULT);
            $config = load_json_assoc(data_path('config.json'));
            $config['password_hash'] = $hash;
            save_json(data_path('config.json'), $config);
            $_SESSION['admin_logged_in'] = true;
            header('Location: /admin/');
            exit;
        }
    }
    // Show setup page
    $page_title = 'Admin Setup';
    require __DIR__ . '/../includes/header.php';
    ?>
    <div class="login-wrap">
      <div class="login-card">
        <div class="login-title">First-Time Setup</div>
        <div class="login-subtitle">Create a password for the admin panel.</div>
        <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="action" value="setup">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" autocomplete="new-password" required minlength="8">
            <div class="form-hint">Minimum 8 characters.</div>
          </div>
          <div class="form-group">
            <label class="form-label" for="password2">Confirm Password</label>
            <input class="form-input" type="password" id="password2" name="password2" autocomplete="new-password" required minlength="8">
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;">Create Password &amp; Log In</button>
        </form>
      </div>
    </div>
    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// ── Handle login ───────────────────────────────────────────
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
        $pw = $_POST['password'] ?? '';
        if (admin_login($pw)) {
            header('Location: /admin/');
            exit;
        }
        $error = 'Incorrect password. Please try again.';
    }
    $page_title = 'Admin Login';
    require __DIR__ . '/../includes/header.php';
    ?>
    <div class="login-wrap">
      <div class="login-card">
        <div class="login-title">Admin Panel</div>
        <div class="login-subtitle">Sign in to manage the DFW Pinball League website.</div>
        <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>
        <form method="post">
          <input type="hidden" name="action" value="login">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" autocomplete="current-password" autofocus required>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;">Sign In</button>
        </form>
      </div>
    </div>
    <?php
    require __DIR__ . '/../includes/footer.php';
    exit;
}

// ── Dashboard (logged in) ──────────────────────────────────
$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';

$sections = [
    ['icon' => '🏠', 'label' => 'Home Page',      'desc' => 'Edit the hero, about text, and quick facts.',      'href' => '/admin/home.php'],
    ['icon' => '📅', 'label' => 'Events',          'desc' => 'Add, edit, or remove tournament events.',          'href' => '/admin/events.php'],
    ['icon' => '🏆', 'label' => 'Sponsors',        'desc' => 'Manage sponsors and venue listings.',              'href' => '/admin/sponsors.php'],
    ['icon' => '🔗', 'label' => 'Links',           'desc' => 'Manage community and official links.',             'href' => '/admin/links.php'],
    ['icon' => '🔑', 'label' => 'Change Password', 'desc' => 'Update the admin panel password.',                 'href' => '/admin/password.php'],
];
?>

<div class="admin-wrap">
  <aside class="admin-sidebar">
    <span class="admin-sidebar-label">Admin</span>
    <ul class="admin-nav">
      <li><a href="/admin/" class="active"><span class="admin-nav-icon">🏁</span> Dashboard</a></li>
      <li><a href="/admin/home.php"><span class="admin-nav-icon">🏠</span> Home Page</a></li>
      <li><a href="/admin/events.php"><span class="admin-nav-icon">📅</span> Events</a></li>
      <li><a href="/admin/sponsors.php"><span class="admin-nav-icon">🏆</span> Sponsors</a></li>
      <li><a href="/admin/links.php"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Dashboard</div>
    <div class="admin-page-desc">Welcome back! What would you like to update today?</div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;">
      <?php foreach ($sections as $s): ?>
      <a href="<?= esc($s['href']) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:24px;text-decoration:none;display:block;transition:border-color .15s,box-shadow .15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--accent-subtle)'" onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        <div style="font-size:28px;margin-bottom:10px;"><?= $s['icon'] ?></div>
        <div style="font-weight:600;font-size:15px;color:var(--text);margin-bottom:4px;"><?= esc($s['label']) ?></div>
        <div style="font-size:13px;color:var(--muted);"><?= esc($s['desc']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="/" target="_blank" class="btn btn-secondary btn-sm">&#8599; View Site</a>
      <a href="/standings.php" target="_blank" class="btn btn-secondary btn-sm">&#8599; View Standings</a>
      <a href="/admin/logout.php" class="btn btn-secondary btn-sm">Log Out</a>
    </div>
  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
