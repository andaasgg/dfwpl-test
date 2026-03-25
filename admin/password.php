<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$active_page = '';
$page_title  = 'Change Password';
$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $new2    = $_POST['new_password2']    ?? '';

    $config = load_json_assoc(data_path('config.json'));
    $hash   = $config['password_hash'] ?? null;

    if (!password_verify($current, $hash)) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $new2) {
        $error = 'New passwords do not match.';
    } else {
        $config['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
        if (save_json(data_path('config.json'), $config)) {
            $msg = 'Password changed successfully.';
        } else {
            $error = 'Could not save. Check write permissions on data/.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="admin-wrap">
  <aside class="admin-sidebar">
    <span class="admin-sidebar-label">Admin</span>
    <ul class="admin-nav">
      <li><a href="/admin/"><span class="admin-nav-icon">🏁</span> Dashboard</a></li>
      <li><a href="/admin/home.php"><span class="admin-nav-icon">🏠</span> Home Page</a></li>
      <li><a href="/admin/events.php"><span class="admin-nav-icon">📅</span> Events</a></li>
      <li><a href="/admin/sponsors.php"><span class="admin-nav-icon">🏆</span> Sponsors</a></li>
      <li><a href="/admin/links.php"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php" class="active"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Change Password</div>
    <div class="admin-page-desc">Update the password used to access the admin panel.</div>

    <?php if ($msg):   ?><div class="alert alert-success"><?= esc($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

    <div class="edit-form-card" style="max-width:420px;">
      <form method="post">
        <div class="form-group">
          <label class="form-label" for="current_pw">Current Password</label>
          <input class="form-input" type="password" id="current_pw" name="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
          <label class="form-label" for="new_pw">New Password</label>
          <input class="form-input" type="password" id="new_pw" name="new_password" required minlength="8" autocomplete="new-password">
          <div class="form-hint">Minimum 8 characters.</div>
        </div>
        <div class="form-group">
          <label class="form-label" for="new_pw2">Confirm New Password</label>
          <input class="form-input" type="password" id="new_pw2" name="new_password2" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Change Password</button>
        </div>
      </form>
    </div>
  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
