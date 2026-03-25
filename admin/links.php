<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$active_page = '';
$page_title  = 'Edit Links';
$msg   = '';
$error = '';

$links    = load_json(data_path('links.json'));
$action   = $_REQUEST['action'] ?? 'list';
$edit_id  = isset($_GET['id']) ? (int)$_GET['id'] : null;

$categories = ['official' => 'Official', 'community' => 'Community', 'streamers' => 'Streamers', 'social' => 'Social Media', 'other' => 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';

    if ($post_action === 'save') {
        $id    = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $entry = [
            'id'          => $id ?? next_id($links),
            'label'       => trim($_POST['label']       ?? ''),
            'url'         => trim($_POST['url']         ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category'    => $_POST['category'] ?? 'other',
        ];

        if (empty($entry['label']) || empty($entry['url'])) {
            $error = 'Label and URL are required.';
            $action = 'edit'; $edit_id = $id;
        } else {
            if ($id !== null) {
                foreach ($links as &$l) { if ($l['id'] === $id) { $l = $entry; break; } }
                unset($l);
            } else {
                $links[] = $entry;
            }
            if (save_json(data_path('links.json'), $links)) {
                $msg = $id !== null ? 'Link updated.' : 'Link added.';
                $action = 'list';
            } else {
                $error = 'Could not save. Check write permissions on data/.'; $action = 'edit'; $edit_id = $id;
            }
        }
    }

    if ($post_action === 'delete') {
        $id    = (int)($_POST['id'] ?? 0);
        $links = array_values(array_filter($links, fn($l) => $l['id'] !== $id));
        if (save_json(data_path('links.json'), $links)) $msg = 'Link removed.';
        $action = 'list';
    }
}

$edit_item = [];
if ($action === 'edit' && $edit_id !== null) {
    foreach ($links as $l) { if ($l['id'] === $edit_id) { $edit_item = $l; break; } }
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
      <li><a href="/admin/links.php" class="active"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Links</div>
    <div class="admin-page-desc">Manage links that appear on the Sponsors &amp; Links page — official sites, community resources, streamers, and social media.</div>

    <?php if ($msg):   ?><div class="alert alert-success"><?= esc($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

    <?php if ($action === 'edit' || $action === 'add'): ?>
    <div class="edit-form-card">
      <div class="edit-form-title"><?= $edit_id ? 'Edit Link' : 'Add Link' ?></div>
      <form method="post">
        <input type="hidden" name="action" value="save">
        <?php if ($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="l_label">Label *</label>
          <input class="form-input" type="text" id="l_label" name="label"
                 value="<?= esc($edit_item['label'] ?? '') ?>"
                 placeholder="IFPA — International Flipper Pinball Association" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="l_url">URL *</label>
          <input class="form-input" type="url" id="l_url" name="url"
                 value="<?= esc($edit_item['url'] ?? '') ?>"
                 placeholder="https://..." required>
        </div>

        <div class="form-group">
          <label class="form-label" for="l_desc">Description (optional)</label>
          <input class="form-input" type="text" id="l_desc" name="description"
                 value="<?= esc($edit_item['description'] ?? '') ?>"
                 placeholder="Short description of the link">
        </div>

        <div class="form-group">
          <label class="form-label" for="l_cat">Category</label>
          <select class="form-select" id="l_cat" name="category">
            <?php foreach ($categories as $val => $label): ?>
              <option value="<?= esc($val) ?>" <?= ($edit_item['category'] ?? 'other') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-hint">Links are grouped by category on the public page.</div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= $edit_id ? 'Save Changes' : 'Add Link' ?></button>
          <a href="/admin/links.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div style="margin-bottom:20px;">
      <a href="?action=add" class="btn btn-primary">+ Add Link</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($links)): ?>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Label</th><th>Category</th><th style="text-align:right;">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($links as $l): ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:13px;"><?= esc($l['label'] ?? '') ?></div>
              <?php if (!empty($l['url'])): ?>
                <div style="font-size:11px;font-family:'DM Mono',monospace;color:var(--muted);"><?= esc($l['url']) ?></div>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--muted);"><?= esc($categories[$l['category'] ?? 'other'] ?? ($l['category'] ?? '')) ?></td>
            <td>
              <div class="td-actions">
                <a href="?action=edit&id=<?= (int)$l['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Remove this link?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id"     value="<?= (int)$l['id'] ?>">
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
    <div class="events-empty">No links yet.</div>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
