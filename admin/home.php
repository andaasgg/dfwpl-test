<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$active_page = '';
$page_title  = 'Edit Home Page';
$msg   = '';
$error = '';

$home = load_json_assoc(data_path('home.json'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home['hero_eyebrow']       = trim($_POST['hero_eyebrow']       ?? '');
    $home['hero_title']         = trim($_POST['hero_title']         ?? '');
    $home['hero_tagline']       = trim($_POST['hero_tagline']       ?? '');
    $home['about_title']        = trim($_POST['about_title']        ?? '');
    $home['about_body']         = trim(str_replace("\r\n", "\n", $_POST['about_body'] ?? ''));
    $home['stat_founded']       = trim($_POST['stat_founded']       ?? '');
    $home['stat_cost']          = trim($_POST['stat_cost']          ?? '');
    $home['stat_events']        = trim($_POST['stat_events']        ?? '');
    $home['stat_label_founded'] = trim($_POST['stat_label_founded'] ?? '');
    $home['stat_label_cost']    = trim($_POST['stat_label_cost']    ?? '');
    $home['stat_label_events']  = trim($_POST['stat_label_events']  ?? '');
    $home['footer_text']        = trim($_POST['footer_text']        ?? '');

    // Quick facts — split by newline, trim each
    $raw_items = trim($_POST['info_items'] ?? '');
    $home['info_items'] = array_values(array_filter(array_map('trim', explode("\n", $raw_items))));

    // Hero image — handle upload or removal
    if (!empty($_POST['remove_hero_image'])) {
        $home['hero_image'] = '';
    } elseif (!empty($_FILES['hero_image']['name'])) {
        $path = handle_upload($_FILES['hero_image'], uploads_path('hero'));
        if ($path) {
            $home['hero_image'] = $path;
        } else {
            $error = 'Image upload failed. Make sure the file is a JPG/PNG/GIF/WebP under 5 MB.';
        }
    }

    if (!$error) {
        if (save_json(data_path('home.json'), $home)) {
            $msg = 'Home page saved successfully.';
        } else {
            $error = 'Could not save file. Check server write permissions on the data/ directory.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
$info_items_text = implode("\n", $home['info_items'] ?? []);
?>

<div class="admin-wrap">
  <aside class="admin-sidebar">
    <span class="admin-sidebar-label">Admin</span>
    <ul class="admin-nav">
      <li><a href="/admin/"><span class="admin-nav-icon">🏁</span> Dashboard</a></li>
      <li><a href="/admin/home.php" class="active"><span class="admin-nav-icon">🏠</span> Home Page</a></li>
      <li><a href="/admin/events.php"><span class="admin-nav-icon">📅</span> Events</a></li>
      <li><a href="/admin/sponsors.php"><span class="admin-nav-icon">🏆</span> Sponsors</a></li>
      <li><a href="/admin/links.php"><span class="admin-nav-icon">🔗</span> Links</a></li>
      <li><a href="/admin/password.php"><span class="admin-nav-icon">🔑</span> Password</a></li>
      <li><a href="/admin/logout.php"><span class="admin-nav-icon">↩</span> Log Out</a></li>
    </ul>
  </aside>

  <main class="admin-content">
    <div class="admin-page-title">Home Page</div>
    <div class="admin-page-desc">Edit the hero section, about text, and quick stats that appear on the home page.</div>

    <?php if ($msg):   ?><div class="alert alert-success"><?= esc($msg)   ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= esc($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data">

      <div class="edit-form-card">
        <div class="edit-form-title">Hero Section</div>

        <div class="form-group">
          <label class="form-label">Hero Banner Image</label>
          <?php $current_img = $home['hero_image'] ?? ''; ?>
          <?php if ($current_img): ?>
            <div style="margin-bottom:12px;">
              <img src="<?= esc($current_img) ?>" alt="Current hero image"
                   style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--border);object-fit:cover;">
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted);margin-bottom:10px;cursor:pointer;">
              <input type="checkbox" name="remove_hero_image" value="1"> Remove current image
            </label>
          <?php endif; ?>
          <input class="form-input" type="file" name="hero_image" accept="image/jpeg,image/png,image/gif,image/webp"
                 style="padding:6px 10px;">
          <div class="form-hint">JPG, PNG, GIF or WebP · max 5 MB · recommended: at least 1400×500px wide. Leave blank to keep the current image.</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="hero_eyebrow">Eyebrow Text</label>
          <input class="form-input" type="text" id="hero_eyebrow" name="hero_eyebrow"
                 value="<?= esc($home['hero_eyebrow'] ?? '') ?>" placeholder="Est. 2015 · Dallas–Fort Worth">
          <div class="form-hint">Small text that appears above the main title.</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="hero_title">Page Title</label>
          <input class="form-input" type="text" id="hero_title" name="hero_title"
                 value="<?= esc($home['hero_title'] ?? '') ?>" placeholder="DFW Pinball League">
        </div>

        <div class="form-group">
          <label class="form-label" for="hero_tagline">Tagline</label>
          <textarea class="form-textarea" id="hero_tagline" name="hero_tagline"
                    rows="3" placeholder="Low cost & family friendly competitive pinball…"><?= esc($home['hero_tagline'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="edit-form-card">
        <div class="edit-form-title">About Section</div>

        <div class="form-group">
          <label class="form-label" for="about_title">Section Title</label>
          <input class="form-input" type="text" id="about_title" name="about_title"
                 value="<?= esc($home['about_title'] ?? '') ?>" placeholder="About the League">
        </div>

        <div class="form-group">
          <label class="form-label" for="about_body">About Text</label>
          <textarea class="form-textarea" id="about_body" name="about_body"
                    rows="8"><?= esc($home['about_body'] ?? '') ?></textarea>
          <div class="form-hint">Separate paragraphs with a blank line.</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="info_items">Quick Facts</label>
          <textarea class="form-textarea" id="info_items" name="info_items"
                    rows="6" placeholder="Adults typically pay around $15 per event&#10;Kids 16 and under play free at most events"><?= esc($info_items_text) ?></textarea>
          <div class="form-hint">One fact per line. These appear as bullet points next to the about text.</div>
        </div>
      </div>

      <div class="edit-form-card">
        <div class="edit-form-title">Stats Bar</div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label" for="stat_founded">Stat 1 — Value</label>
            <input class="form-input" type="text" id="stat_founded" name="stat_founded"
                   value="<?= esc($home['stat_founded'] ?? '') ?>" placeholder="2015">
          </div>
          <div class="form-group">
            <label class="form-label" for="stat_label_founded">Stat 1 — Label</label>
            <input class="form-input" type="text" id="stat_label_founded" name="stat_label_founded"
                   value="<?= esc($home['stat_label_founded'] ?? '') ?>" placeholder="Year Founded">
          </div>
          <div class="form-group">
            <label class="form-label" for="stat_cost">Stat 2 — Value</label>
            <input class="form-input" type="text" id="stat_cost" name="stat_cost"
                   value="<?= esc($home['stat_cost'] ?? '') ?>" placeholder="$15">
          </div>
          <div class="form-group">
            <label class="form-label" for="stat_label_cost">Stat 2 — Label</label>
            <input class="form-input" type="text" id="stat_label_cost" name="stat_label_cost"
                   value="<?= esc($home['stat_label_cost'] ?? '') ?>" placeholder="Typical Entry">
          </div>
          <div class="form-group">
            <label class="form-label" for="stat_events">Stat 3 — Value</label>
            <input class="form-input" type="text" id="stat_events" name="stat_events"
                   value="<?= esc($home['stat_events'] ?? '') ?>" placeholder="16+">
          </div>
          <div class="form-group">
            <label class="form-label" for="stat_label_events">Stat 3 — Label</label>
            <input class="form-input" type="text" id="stat_label_events" name="stat_label_events"
                   value="<?= esc($home['stat_label_events'] ?? '') ?>" placeholder="Events / Season">
          </div>
        </div>
      </div>

      <div class="edit-form-card">
        <div class="edit-form-title">Footer</div>
        <div class="form-group">
          <label class="form-label" for="footer_text">Footer Text</label>
          <input class="form-input" type="text" id="footer_text" name="footer_text"
                 value="<?= esc($home['footer_text'] ?? '') ?>"
                 placeholder="Contact: marx.pinball@gmail.com · Follow us on Facebook">
          <div class="form-hint">Optional line shown above the copyright in the footer. Good for contact info or social links.</div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a href="/" target="_blank" class="btn btn-secondary">Preview Site &#8599;</a>
      </div>

    </form>
  </main>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
