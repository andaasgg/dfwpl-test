<?php
require_once __DIR__ . '/includes/functions.php';

$active_page = 'events';
$page_title  = 'Events';

$events = load_json(data_path('events.json'));

// Filter visible, sort by date
$visible = array_filter($events, fn($e) => ($e['visible'] ?? true));
usort($visible, fn($a, $b) => strcmp($a['date'], $b['date']));

$today    = date('Y-m-d');
$upcoming = array_values(array_filter($visible, fn($e) => $e['date'] >= $today));
$past     = array_reverse(array_values(array_filter($visible, fn($e) => $e['date'] < $today)));

require __DIR__ . '/includes/header.php';
?>

<div class="page-narrow">

  <h1 style="font-family:'Bebas Neue',sans-serif;font-size:44px;letter-spacing:.04em;margin-bottom:8px;">Events</h1>
  <p style="font-size:14px;color:var(--muted);margin-bottom:36px;">
    Upcoming and past DFW Pinball League tournaments.
    Qualify for the championship by playing 5 or more league events.
  </p>

  <?php if (!empty($upcoming)): ?>
  <div class="section">
    <h2 class="section-title">Upcoming</h2>
    <div class="events-list">
      <?php foreach ($upcoming as $e):
        $dt   = new DateTime($e['date']);
        $url  = $e['url'] ?? '';
        $tag  = $url ? 'a' : 'div';
        $href = $url ? ' href="' . esc($url) . '" target="_blank" rel="noopener"' : '';
        $extra = $url ? ' has-url' : '';
      ?>
      <<?= $tag ?> class="event-card<?= $extra ?>"<?= $href ?>>
        <div class="event-date-block">
          <div class="event-date-month"><?= $dt->format('M') ?></div>
          <div class="event-date-day"><?= $dt->format('j') ?></div>
          <div class="event-date-year"><?= $dt->format('Y') ?></div>
        </div>
        <div class="event-info">
          <div class="event-name"><?= esc($e['name']) ?></div>
          <?php if (!empty($e['venue'])): ?>
            <div class="event-venue">
              <?= esc($e['venue']) ?><?= !empty($e['address']) ? ' · ' . esc($e['address']) : '' ?>
            </div>
          <?php endif; ?>
          <div class="event-tags">
            <?php if (!empty($e['cost'])): ?>
              <span class="event-tag cost"><?= esc($e['cost']) ?></span>
            <?php endif; ?>
            <?php if (!empty($e['description'])): ?>
              <span class="event-tag"><?= esc(mb_strimwidth($e['description'], 0, 60, '…')) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($url): ?><div class="event-arrow">&#8599;</div><?php endif; ?>
      </<?= $tag ?>>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="events-empty">
    <div style="font-size:32px;margin-bottom:12px;">📅</div>
    No upcoming events scheduled yet. Check back soon!
  </div>
  <?php endif; ?>

  <?php if (!empty($past)): ?>
  <div class="section">
    <h2 class="section-title">Past Events</h2>
    <div class="events-list">
      <?php foreach ($past as $e):
        $dt  = new DateTime($e['date']);
        $url = $e['url'] ?? '';
        $tag = $url ? 'a' : 'div';
        $href = $url ? ' href="' . esc($url) . '" target="_blank" rel="noopener"' : '';
      ?>
      <<?= $tag ?> class="event-card past"<?= $href ?>>
        <div class="event-date-block">
          <div class="event-date-month"><?= $dt->format('M') ?></div>
          <div class="event-date-day"><?= $dt->format('j') ?></div>
          <div class="event-date-year"><?= $dt->format('Y') ?></div>
        </div>
        <div class="event-info">
          <div class="event-name"><?= esc($e['name']) ?></div>
          <?php if (!empty($e['venue'])): ?>
            <div class="event-venue"><?= esc($e['venue']) ?></div>
          <?php endif; ?>
        </div>
        <?php if ($url): ?><div class="event-arrow">&#8599;</div><?php endif; ?>
      </<?= $tag ?>>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
