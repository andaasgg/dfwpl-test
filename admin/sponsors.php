<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$active_page = '';
$page_title  = 'Edit Sponsors';
$msg   = '';
$error = '';

$sponsors = load_json(data_path('sponsors.json'));
$action   = $_REQUEST['action'] ?? 'list';
$edit_id  = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';

    if ($post_action === 'save') {
        $id  = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $existing_logo = '';
        if ($id !== null) {
            foreach ($sponsors as $s) { if ($s['id'] === $id) { $existing_logo = $s['logo_url'] ?? ''; break; } }
        }

        // Logo: prefer uploaded file, then typed URL, then keep existing
        $logo = $existing_logo;
        if (!empty($_POST['remove_logo'])) {
            $logo = '';
        } elseif (!empty($_FILES['logo_file']['name'])) {
            $uploaded = handle_upload($_FILES['logo_file'], uploads_path('sponsors'));
            $logo = $uploaded ?: $existing_logo;
        } elseif (trim($_POST['logo_url'] ?? '') !== '') {
            $logo = trim($_POST['logo_url']);
        }

        $entry = [
            'id'          => $id ?? next_id($sponsors),
            'name'        => trim($_POST['name']        ?? ''),
            'url'         => trim($_POST['url']         ?? ''),
            'logo_url'    => $logo,
            'description' => trim($_POST['description'] ?? ''),
        ];

        if (empty($entry['name'])) {
            $error = 'Name is required.';
            $action = 'edit'; $edit_id = $id;
        } else {
            if ($id !== null) {
                foreach ($sponsors as &$s) { if ($s['id'] === $id) { $s = $entry; break; } }
                unset($s);
            } else {
                $sponsors[] = $entry;
            }
            if (save_json(data_path('sponsors.json'), $sponsors)) {
                $msg = $id !== null ? 'Sponsor updated.' : 'Sponsor added.';
                $action = 'list';
            } else {
                $error = 'Could not save. Check write permissions on data/.';
                $action = 'edit'; $edit_id = $id;
            }
        }
    }

    if ($post_action === 'delete') {
        $id       = (int)($_POST['id'] ?? 0);
        $sponsors = array_values(array_filter($sponsors, fn($s) => $s['id'] !== $id));
        if (save_json(data_path('sponsors.json'), $sponsors)) $msg = 'Sponsor removed.';
        $action = 'list';
    }
}

$edit_item = [];
if ($action === 'edit' && $edit_id !== null) {
    foreach ($sponsors as $s) { if ($s['id'] === $edit_id) { $edit_item = $s; break; } }
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
      <li><a href="/admin/sponsors.php" class="active"><span class="admin-nav-icon">🏆</span> Sponsors</a></li>
      <li><a href="/admin/links.php"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Sponsors</div>
    <div class="admin-page-desc">Manage the sponsors and venues shown on the Sponsors &amp; Links page.</div>

    <?php if ($msg):   ?><div class="alert alert-success"><?= esc($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

    <?php if ($action === 'edit' || $action === 'add'): ?>
    <div class="edit-form-card">
      <div class="edit-form-title"><?= $edit_id ? 'Edit Sponsor' : 'Add Sponsor' ?></div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <?php if ($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="s_name">Name *</label>
          <input class="form-input" type="text" id="s_name" name="name"
                 value="<?= esc($edit_item['name'] ?? '') ?>" placeholder="Carpool Pinball" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="s_url">Website URL</label>
          <input class="form-input" type="url" id="s_url" name="url"
                 value="<?= esc($edit_item['url'] ?? '') ?>" placeholder="https://...">
        </div>

        <div class="form-group">
          <label class="form-label">Logo Image</label>
          <?php $cur_logo = $edit_item['logo_url'] ?? ''; ?>
          <?php if ($cur_logo): ?>
            <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px;">
              <img src="<?= esc($cur_logo) ?>" alt="Logo"
                   style="max-width:120px;max-height:60px;object-fit:contain;border:1px solid var(--border);border-radius:6px;padding:4px;background:#fff;">
              <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);cursor:pointer;">
                <input type="checkbox" name="remove_logo" value="1"> Remove logo
              </label>
            </div>
          <?php endif; ?>
          <input class="form-input" type="file" name="logo_file" accept="image/jpeg,image/png,image/gif,image/webp"
                 style="padding:6px 10px;margin-bottom:8px;">
          <div class="form-hint" style="margin-bottom:8px;">Upload a logo (JPG/PNG/GIF/WebP, max 5 MB). Or paste a URL below.</div>
          <input class="form-input" type="url" id="s_logo" name="logo_url"
                 value="<?= esc(!str_starts_with($cur_logo, '/assets/uploads/') ? $cur_logo : '') ?>"
                 placeholder="https://example.com/logo.png">
          <div class="form-hint">Leave both blank and a pinball emoji will be shown.</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="s_desc">Description</label>
          <input class="form-input" type="text" id="s_desc" name="description"
                 value="<?= esc($edit_item['description'] ?? '') ?>"
                 placeholder="Our home venue — competitive pinball in Dallas">
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= $edit_id ? 'Save Changes' : 'Add Sponsor' ?></button>
          <a href="/admin/sponsors.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div style="margin-bottom:20px;">
      <a href="?action=add" class="btn btn-primary">+ Add Sponsor</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($sponsors)): ?>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Logo</th><th>Name</th><th>Website</th><th style="text-align:right;">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($sponsors as $s): ?>
          <tr>
            <td style="width:64px;">
              <?php if (!empty($s['logo_url'])): ?>
                <img src="<?= esc($s['logo_url']) ?>" alt="" style="max-width:56px;max-height:32px;object-fit:contain;">
              <?php else: ?>
                <span style="font-size:20px;">🎰</span>
              <?php endif; ?>
            </td>
            <td style="font-weight:600;"><?= esc($s['name'] ?? '') ?></td>
            <td style="font-size:12px;font-family:'DM Mono',monospace;color:var(--muted);">
              <?php if (!empty($s['url'])): ?>
                <a href="<?= esc($s['url']) ?>" target="_blank" style="color:var(--accent);"><?= esc($s['url']) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <div class="td-actions">
                <a href="?action=edit&id=<?= (int)$s['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Remove this sponsor?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id"     value="<?= (int)$s['id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="events-empty">No sponsors yet.</div>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
