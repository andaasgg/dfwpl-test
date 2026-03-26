<?php
require_once __DIR__ . '/includes/functions.php';

$active_page = 'home';
$page_title  = 'Home';

$home   = load_json_assoc(data_path('home.json'));
$events = load_json(data_path('events.json'));

// Sort events by date, show next 3 upcoming visible events
$today   = date('Y-m-d');
$upcoming = array_filter($events, fn($e) => ($e['visible'] ?? true) && ($e['date'] ?? '') >= $today);
usort($upcoming, fn($a, $b) => strcmp($a['date'], $b['date']));
$upcoming = array_slice(array_values($upcoming), 0, 3);

require __DIR__ . '/includes/header.php';
?>

<?php
  $hero_img = $home['hero_image'] ?? '';
  $hero_has_image = !empty($hero_img);
?>

<?php if ($hero_has_image): ?>
<!-- Full-bleed hero banner -->
<div class="hero hero-banner" style="background-image:url('<?= esc($hero_img) ?>')">
  <div class="hero-inner">
    <div class="hero-eyebrow"><?= esc($home['hero_eyebrow'] ?? 'Est. 2015 · Dallas–Fort Worth') ?></div>
    <div class="hero-title"><?= esc($home['hero_title'] ?? 'DFW Pinball League') ?></div>
    <p class="hero-tagline"><?= esc($home['hero_tagline'] ?? '') ?></p>
    <div class="hero-actions">
      <a href="/events.php" class="btn btn-primary">View Events</a>
      <a href="/standings.php" class="btn btn-secondary btn-secondary-light">Live Standings</a>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="page">

  <?php if (!$hero_has_image): ?>
  <!-- Card hero (no banner image) -->
  <div class="hero">
    <div class="hero-eyebrow"><?= esc($home['hero_eyebrow'] ?? 'Est. 2015 · Dallas–Fort Worth') ?></div>
    <div class="hero-title"><?= esc($home['hero_title'] ?? 'DFW Pinball League') ?></div>
    <p class="hero-tagline"><?= esc($home['hero_tagline'] ?? '') ?></p>
    <div class="hero-actions">
      <a href="/events.php" class="btn btn-primary">View Events</a>
      <a href="/standings.php" class="btn btn-secondary">Live Standings</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-val"><?= esc($home['stat_founded'] ?? '2015') ?></div>
      <div class="stat-card-label"><?= esc($home['stat_label_founded'] ?? 'Year Founded') ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-val"><?= esc($home['stat_cost'] ?? '$15') ?></div>
      <div class="stat-card-label"><?= esc($home['stat_label_cost'] ?? 'Typical Entry') ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-val"><?= esc($home['stat_events'] ?? '16+') ?></div>
      <div class="stat-card-label"><?= esc($home['stat_label_events'] ?? 'Events / Season') ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-val">Free</div>
      <div class="stat-card-label">Kids 16 &amp; Under</div>
    </div>
  </div>

  <!-- About -->
  <?php
    $about_img = $home['about_image'] ?? '';
    $about_img_pos = $home['about_image_position'] ?? 'right';
  ?>
  <div class="section">
    <h2 class="section-title"><?= esc($home['about_title'] ?? 'About') ?></h2>

    <?php if ($about_img && $about_img_pos === 'top'): ?>
      <img src="<?= esc($about_img) ?>" alt="" class="about-img about-img-top">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start;">
      <div class="about-text">
        <?php if ($about_img && $about_img_pos === 'right'): ?>
          <img src="<?= esc($about_img) ?>" alt="" class="about-img about-img-right">
        <?php endif; ?>
        <?php foreach (explode("\n\n", str_replace("\r\n", "\n", $home['about_body'] ?? '')) as $para): ?>
          <?php if (trim($para)): ?><p><?= render_md(trim($para)) ?></p><?php endif; ?>
        <?php endforeach; ?>
        <?php if ($about_img && $about_img_pos === 'bottom'): ?>
          <img src="<?= esc($about_img) ?>" alt="" class="about-img about-img-bottom">
        <?php endif; ?>
      </div>
      <div class="card" style="background:var(--surface2);">
        <div style="font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.15em;text-transform:uppercase;color:var(--accent);margin-bottom:14px;">Quick Facts</div>
        <div class="info-list">
          <?php foreach (($home['info_items'] ?? []) as $item): ?>
            <div class="info-item"><?= render_md($item) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Upcoming Events Preview -->
  <?php if (!empty($upcoming)): ?>
  <div class="section">
    <h2 class="section-title">Upcoming Events</h2>
    <div class="events-list">
      <?php foreach ($upcoming as $e):
        $dt  = new DateTime($e['date']);
        $url = $e['url'] ?? '';
      ?>
      <div class="event-card">
        <div class="event-header">
          <div class="event-date-block">
            <div class="event-date-month"><?= $dt->format('M') ?></div>
            <div class="event-date-day"><?= $dt->format('j') ?></div>
            <div class="event-date-year"><?= $dt->format('Y') ?></div>
          </div>
          <div class="event-info">
            <div class="event-name"><?= esc($e['name']) ?></div>
            <?php if (!empty($e['venue'])): ?>
              <div class="event-venue"><?= esc($e['venue']) ?><?= !empty($e['address']) ? ' · ' . esc($e['address']) : '' ?></div>
            <?php endif; ?>
            <div class="event-tags">
              <?php if (!empty($e['cost'])): ?>
                <span class="event-tag cost"><?= esc($e['cost']) ?></span>
              <?php endif; ?>
              <?php if (!empty($e['brief'])): ?>
                <span class="event-tag"><?= esc($e['brief']) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($url): ?>
          <div class="event-actions">
            <a class="event-link-btn" href="<?= esc($url) ?>" target="_blank" rel="noopener" title="More info">&#8599;</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:16px;">
      <a href="/events.php" class="btn btn-secondary btn-sm">All Events →</a>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
