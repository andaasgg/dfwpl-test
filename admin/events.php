<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$active_page = '';
$page_title  = 'Edit Events';
$msg   = '';
$error = '';

$events = load_json(data_path('events.json'));
$action = $_REQUEST['action'] ?? 'list';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ── Handle POST actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';

    if ($post_action === 'save') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;

        // Event photo — handle upload or keep existing
        $existing_photo = '';
        if ($id !== null) {
            foreach ($events as $e) { if ($e['id'] === $id) { $existing_photo = $e['photo_url'] ?? ''; break; } }
        }
        $photo = $existing_photo;
        if (!empty($_POST['remove_photo'])) {
            $photo = '';
        } elseif (!empty($_FILES['photo_file']['name'])) {
            $uploaded = handle_upload($_FILES['photo_file'], uploads_path('events'));
            if ($uploaded) $photo = $uploaded;
        }

        $entry = [
            'id'          => $id ?? next_id($events),
            'name'        => trim($_POST['name']        ?? ''),
            'date'        => trim($_POST['date']        ?? ''),
            'venue'       => trim($_POST['venue']       ?? ''),
            'address'     => trim($_POST['address']     ?? ''),
            'cost'        => trim($_POST['cost']        ?? ''),
            'brief'       => trim($_POST['brief']       ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'url'         => trim($_POST['url']         ?? ''),
            'photo_url'   => $photo,
            'visible'     => isset($_POST['visible']),
        ];

        if (empty($entry['name'])) {
            $error  = 'Event name is required.';
            $action = 'edit';
            $edit_id = $id;
        } else {
            if ($id !== null) {
                // Update existing
                foreach ($events as &$e) {
                    if ($e['id'] === $id) { $e = $entry; break; }
                }
                unset($e);
            } else {
                $events[] = $entry;
            }

            if (save_json(data_path('events.json'), $events)) {
                $msg    = $id !== null ? 'Event updated.' : 'Event added.';
                $action = 'list';
            } else {
                $error  = 'Could not save file. Check write permissions on data/.';
                $action = 'edit';
                $edit_id = $id;
            }
        }
    }

    if ($post_action === 'delete') {
        $id     = (int)($_POST['id'] ?? 0);
        $events = array_values(array_filter($events, fn($e) => $e['id'] !== $id));
        if (save_json(data_path('events.json'), $events)) {
            $msg = 'Event deleted.';
        }
        $action = 'list';
    }
}

// Sort for display
usort($events, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

// Find event being edited
$edit_event = [];
if ($action === 'edit' && $edit_id !== null) {
    foreach ($events as $e) {
        if ($e['id'] === $edit_id) { $edit_event = $e; break; }
    }
}

require __DIR__ . '/../includes/header.php';
$today = date('Y-m-d');
?>

<div class="admin-wrap">
  <aside class="admin-sidebar">
    <span class="admin-sidebar-label">Admin</span>
    <ul class="admin-nav">
      <li><a href="/admin/"><span class="admin-nav-icon">🏁</span> Dashboard</a></li>
      <li><a href="/admin/home.php"><span class="admin-nav-icon">🏠</span> Home Page</a></li>
      <li><a href="/admin/events.php" class="active"><span class="admin-nav-icon">📅</span> Events</a></li>
      <li><a href="/admin/sponsors.php"><span class="admin-nav-icon">🏆</span> Sponsors</a></li>
      <li><a href="/admin/links.php"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Events</div>
    <div class="admin-page-desc">Add and manage upcoming tournaments. Events with a past date are shown faded on the public site.</div>

    <?php if ($msg):   ?><div class="alert alert-success"><?= esc($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

    <!-- ── Add / Edit Form ── -->
    <?php if ($action === 'edit' || $action === 'add'): ?>
    <div class="edit-form-card">
      <div class="edit-form-title"><?= $edit_id ? 'Edit Event' : 'Add New Event' ?></div>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <?php if ($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>

        <div class="form-row cols-2">
          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label" for="e_name">Event Name *</label>
            <input class="form-input" type="text" id="e_name" name="name"
                   value="<?= esc($edit_event['name'] ?? '') ?>"
                   placeholder="DFW Pinball League Monthly Event" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="e_date">Date *</label>
            <input class="form-input" type="date" id="e_date" name="date"
                   value="<?= esc($edit_event['date'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="e_cost">Cost / Entry Fee</label>
            <input class="form-input" type="text" id="e_cost" name="cost"
                   value="<?= esc($edit_event['cost'] ?? '') ?>"
                   placeholder="$15 adults · Kids 16 & under free">
          </div>

          <div class="form-group">
            <label class="form-label" for="e_venue">Venue Name</label>
            <input class="form-input" type="text" id="e_venue" name="venue"
                   value="<?= esc($edit_event['venue'] ?? '') ?>"
                   placeholder="Carpool Pinball">
          </div>

          <div class="form-group">
            <label class="form-label" for="e_address">Address (optional)</label>
            <input class="form-input" type="text" id="e_address" name="address"
                   value="<?= esc($edit_event['address'] ?? '') ?>"
                   placeholder="Dallas, TX">
          </div>

          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label" for="e_brief">Format / Type (optional)</label>
            <input class="form-input" type="text" id="e_brief" name="brief"
                   value="<?= esc($edit_event['brief'] ?? '') ?>"
                   placeholder="e.g. Match play qualifying · Double-elimination final">
            <div class="form-hint">One-line summary shown on the card — tournament format, special notes, etc.</div>
          </div>

          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label" for="e_desc">Full Description (optional)</label>
            <textarea class="form-textarea" id="e_desc" name="description"
                      rows="6"><?= esc($edit_event['description'] ?? '') ?></textarea>
            <div class="form-hint">Shown when a visitor expands the event. Supports **bold**, *italic*, and [link text](url).</div>
          </div>

          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label" for="e_url">More Info URL (optional)</label>
            <input class="form-input" type="url" id="e_url" name="url"
                   value="<?= esc($edit_event['url'] ?? '') ?>"
                   placeholder="https://...">
            <div class="form-hint">If provided, a ↗ button appears on the card linking to this URL.</div>
          </div>

          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">Event / Venue Photo (optional)</label>
            <?php $cur_photo = $edit_event['photo_url'] ?? ''; ?>
            <?php if ($cur_photo): ?>
              <div style="margin-bottom:10px;">
                <img src="<?= esc($cur_photo) ?>" alt="Event photo"
                     style="max-width:100%;max-height:180px;border-radius:8px;object-fit:cover;border:1px solid var(--border);">
              </div>
              <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);margin-bottom:8px;cursor:pointer;">
                <input type="checkbox" name="remove_photo" value="1"> Remove photo
              </label>
            <?php endif; ?>
            <input class="form-input" type="file" name="photo_file" accept="image/jpeg,image/png,image/gif,image/webp"
                   style="padding:6px 10px;">
            <div class="form-hint">A representative photo for this event or venue. JPG/PNG/GIF/WebP, max 5 MB.</div>
          </div>

          <div class="form-group" style="grid-column:1/-1;">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;">
              <input type="checkbox" name="visible" value="1"
                     <?= ($edit_event['visible'] ?? true) ? 'checked' : '' ?>>
              Show this event on the public website
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= $edit_id ? 'Save Changes' : 'Add Event' ?></button>
          <a href="/admin/events.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div style="margin-bottom:20px;">
      <a href="?action=add" class="btn btn-primary">+ Add Event</a>
    </div>
    <?php endif; ?>

    <!-- ── Event List ── -->
    <?php if (!empty($events)): ?>
    <div class="data-table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Event Name</th>
            <th>Venue</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $e):
            $isPast = ($e['date'] ?? '') < $today;
          ?>
          <tr>
            <td style="font-family:'DM Mono',monospace;font-size:12px;white-space:nowrap;">
              <?= esc($e['date'] ?? '') ?>
              <?php if ($isPast): ?><br><span class="past-label">past</span><?php endif; ?>
              <?php if (!($e['visible'] ?? true)): ?><br><span class="past-label">hidden</span><?php endif; ?>
            </td>
            <td style="font-weight:600;"><?= esc($e['name'] ?? '') ?></td>
            <td style="color:var(--muted);font-size:13px;"><?= esc($e['venue'] ?? '') ?></td>
            <td>
              <div class="td-actions">
                <a href="?action=edit&id=<?= (int)$e['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this event?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id"     value="<?= (int)$e['id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="events-empty">No events yet. Add your first event above.</div>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
