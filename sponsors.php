<?php
require_once __DIR__ . '/includes/functions.php';

$active_page = 'sponsors';
$page_title  = 'Sponsors & Links';

$sponsors = load_json(data_path('sponsors.json'));
$links    = load_json(data_path('links.json'));

// Group links by category
$link_groups = [];
foreach ($links as $link) {
    $cat = $link['category'] ?? 'other';
    $link_groups[$cat][] = $link;
}

$cat_labels = [
    'official'  => 'Official',
    'community' => 'Community',
    'streamers' => 'Streamers',
    'social'    => 'Social Media',
    'other'     => 'Other Links',
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-narrow">

  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:44px;letter-spacing:.04em;margin-bottom:8px;">Sponsors &amp; Links</h1>
  <p style="font-size:14px;color:var(--muted);margin-bottom:36px;">
    Thank you to our sponsors and venues who make DFW Pinball League possible.
  </p>

  <?php if (!empty($sponsors)): ?>
  <div class="section">
    <h2 class="section-title">Sponsors &amp; Venues</h2>
    <div class="sponsor-grid">
      <?php foreach ($sponsors as $s):
        $tag  = !empty($s['url']) ? 'a' : 'div';
        $href = !empty($s['url']) ? ' href="' . esc($s['url']) . '" target="_blank" rel="noopener"' : '';
      ?>
      <<?= $tag ?> class="sponsor-card"<?= $href ?>>
        <?php if (!empty($s['logo_url'])): ?>
          <img src="<?= esc($s['logo_url']) ?>" alt="<?= esc($s['name']) ?>" class="sponsor-logo">
        <?php else: ?>
          <div style="font-size:36px;">🎰</div>
        <?php endif; ?>
        <div class="sponsor-name"><?= esc($s['name']) ?></div>
        <?php if (!empty($s['description'])): ?>
          <div class="sponsor-desc"><?= esc($s['description']) ?></div>
        <?php endif; ?>
      </<?= $tag ?>>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php foreach ($link_groups as $cat => $items): ?>
  <div class="section">
    <h2 class="section-title"><?= esc($cat_labels[$cat] ?? ucfirst($cat)) ?></h2>
    <div class="links-list">
      <?php foreach ($items as $link): ?>
      <a class="link-item" href="<?= esc($link['url']) ?>" target="_blank" rel="noopener">
        <div>
          <div class="link-label"><?= esc($link['label']) ?></div>
          <?php if (!empty($link['description'])): ?>
            <div class="link-desc"><?= esc($link['description']) ?></div>
          <?php endif; ?>
        </div>
        <div class="link-arrow">&#8599;</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($sponsors) && empty($links)): ?>
  <div class="events-empty">No sponsors or links added yet.</div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
