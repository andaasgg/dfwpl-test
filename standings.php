<?php
require_once __DIR__ . '/includes/functions.php';

$active_page = 'standings';
$page_title  = 'Standings';

$api_url = 'https://api.ifpapinball.com/rankings/custom/430?start_pos=1&count=50&api_key=55b97a4ccf9b9c4ee2d443b2737574ab';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
unset($ch);

$data    = null;
$error   = null;
$players = [];
$title   = 'Custom Rankings';
$subtitle = '';
$top_points = 0;
$max_events = 0;

if ($response === false || $http_code !== 200) {
    $error = "Could not load rankings (HTTP $http_code).";
} else {
    $data = json_decode($response, true);
    if ($data === null) {
        $error = 'Invalid response from IFPA API.';
    }
}

if ($data) {
    $title   = $data['title'] ?? 'Custom Rankings';
    $players = $data['view_results'] ?? [];

    $top_points = $players[0]['wppr_points'] ?? 0;
    foreach ($players as $p) {
        $e = $p['event_count'] ?? 0;
        if ($e > $max_events) $max_events = $e;
    }

    $total    = $data['total_count'] ?? count($players);
    $subtitle = $data['description'] ?? "$total ranked players";
}

require __DIR__ . '/includes/header.php';
?>

<style>
  .rankings-wrap {
    max-width: 680px;
    margin: 0 auto;
    padding: 48px 24px 80px;
  }

  .rankings-header {
    background: var(--surface);
    border: 1px solid var(--border);
    border-bottom: none;
    padding: 24px 28px 20px;
    border-radius: 10px 10px 0 0;
    position: relative;
    overflow: hidden;
  }

  .rankings-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), var(--gold), var(--accent));
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
  }

  .rankings-header-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }

  .rankings-eyebrow {
    font-family: 'DM Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 6px;
  }

  .rankings-title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 36px;
    letter-spacing: 0.04em;
    line-height: 1;
    color: var(--text);
  }

  .rankings-subtitle {
    font-size: 13px;
    color: var(--muted);
    margin-top: 6px;
  }

  .live-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--accent-subtle);
    border: 1px solid rgba(217, 58, 16, 0.3);
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 11px;
    font-family: 'DM Mono', monospace;
    color: var(--accent);
    letter-spacing: 0.05em;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .live-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--accent);
    animation: pulse 1.5s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.8); }
  }

  .stats-bar {
    display: flex;
    border: 1px solid var(--border);
    border-top: none;
    border-bottom: none;
    background: var(--surface2);
  }

  .rstat {
    flex: 1;
    padding: 10px 16px;
    text-align: center;
    border-right: 1px solid var(--border);
  }
  .rstat:last-child { border-right: none; }

  .rstat-val {
    font-family: 'Bebas Neue', monospace;
    font-size: 20px;
    color: var(--gold);
    line-height: 1;
  }

  .rstat-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-top: 2px;
  }

  .search-wrap {
    border: 1px solid var(--border);
    border-top: none;
    border-bottom: none;
    background: var(--surface);
    padding: 12px 16px;
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .qualified-toggle {
    flex-shrink: 0;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--muted);
    font-family: 'DM Mono', monospace;
    font-size: 11px;
    padding: 8px 12px;
    cursor: pointer;
    white-space: nowrap;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
  }

  .qualified-toggle:hover {
    border-color: var(--green);
    color: var(--green);
  }

  .qualified-toggle.active {
    background: var(--green-subtle);
    border-color: var(--green);
    color: var(--green);
  }

  .search-input {
    width: 100%;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    padding: 8px 14px 8px 36px;
    outline: none;
    transition: border-color 0.15s;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236b6b80' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 12px center;
  }
  .search-input:focus { border-color: var(--accent); }
  .search-input::placeholder { color: var(--muted); }

  .table-wrap {
    border: 1px solid var(--border);
    background: var(--surface);
    overflow: hidden;
  }

  .table-head {
    display: grid;
    grid-template-columns: 52px 1fr 110px 110px;
    padding: 8px 16px;
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
  }

  .th {
    font-family: 'DM Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--muted);
  }
  .th.right { text-align: right; }

  .row {
    display: grid;
    grid-template-columns: 52px 1fr 110px 110px;
    align-items: center;
    padding: 0 16px;
    height: 52px;
    border-bottom: 1px solid var(--border);
    transition: background 0.12s;
    animation: fadeIn 0.3s ease both;
  }

  .row:last-child { border-bottom: none; }
  .row:hover { background: var(--surface2); }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .row.top-3 { background: rgba(160, 112, 0, 0.05); }
  .row.top-3:hover { background: rgba(160, 112, 0, 0.09); }

  .rank {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 20px;
    letter-spacing: 0.03em;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .rank-num       { color: var(--muted); }
  .rank-num.r1    { color: var(--gold); }
  .rank-num.r2    { color: var(--silver); }
  .rank-num.r3    { color: var(--bronze); }

  .medal { font-size: 14px; line-height: 1; }

  .player { min-width: 0; }

  .player-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .player-meta {
    font-size: 11px;
    color: var(--muted);
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .points {
    text-align: right;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    font-weight: 500;
    color: var(--gold);
  }

  .events-col {
    text-align: right;
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    color: var(--muted);
  }

  .qualified-check {
    color: var(--green);
    font-size: 11px;
    margin-left: 4px;
  }

  .cut-line {
    display: flex;
    align-items: center;
    padding: 0 16px;
    height: 28px;
    background: #fff9f8;
    border-top: 2px dashed var(--accent);
    border-bottom: 2px dashed var(--accent);
  }

  .cut-label {
    font-family: 'DM Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--accent);
  }

  .error-msg {
    padding: 32px 24px;
    text-align: center;
    color: var(--accent);
    font-size: 13px;
  }

  .rankings-footer {
    border: 1px solid var(--border);
    border-top: none;
    background: var(--surface2);
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 10px 10px;
  }

  .rankings-footer-left {
    font-size: 11px;
    color: var(--muted);
    font-family: 'DM Mono', monospace;
  }

  .ifpa-link {
    font-size: 11px;
    color: var(--muted);
    text-decoration: none;
    font-family: 'DM Mono', monospace;
    transition: color 0.15s;
  }
  .ifpa-link:hover { color: var(--accent); }

  .empty {
    padding: 32px 24px;
    text-align: center;
    color: var(--muted);
    font-size: 13px;
  }
</style>

<div class="rankings-wrap">

  <div class="rankings-header">
    <div class="rankings-header-top">
      <div>
        <div class="rankings-eyebrow">IFPA Custom Rankings</div>
        <div class="rankings-title"><?= esc($title) ?></div>
        <div class="rankings-subtitle"><?= esc($subtitle) ?></div>
      </div>
      <div class="live-badge">
        <div class="live-dot"></div>
        LIVE
      </div>
    </div>
  </div>

  <div class="stats-bar">
    <div class="rstat">
      <div class="rstat-val"><?= count($players) ?: '—' ?></div>
      <div class="rstat-label">Players</div>
    </div>
    <div class="rstat">
      <div class="rstat-val"><?= $top_points ? number_format((float)$top_points, 2) : '—' ?></div>
      <div class="rstat-label">Top Points</div>
    </div>
    <div class="rstat">
      <div class="rstat-val"><?= $max_events ?: '—' ?></div>
      <div class="rstat-label">Max Events</div>
    </div>
  </div>

  <div class="search-wrap">
    <input class="search-input" type="text" placeholder="Search players…" id="search" oninput="filterRows()">
    <button class="qualified-toggle" id="qualified-toggle" onclick="toggleQualified()">
      <span class="qualified-check">&#10003;</span> Qualified Only
    </button>
  </div>

  <div class="table-wrap">
    <div class="table-head">
      <div class="th">Rank</div>
      <div class="th">Player</div>
      <div class="th right">Points</div>
      <div class="th right">Events</div>
    </div>
    <div id="table-body">
      <?php if ($error): ?>
        <div class="error-msg">&#9888; <?= esc($error) ?></div>
      <?php elseif (empty($players)): ?>
        <div class="empty">No players found.</div>
      <?php else: ?>
        <?php
          $total_qualified = count(array_filter($players, fn($p) => is_numeric($p['event_count'] ?? 0) && $p['event_count'] >= 5));
          $cut_after       = min(32, $total_qualified);
          $qualified_count = 0;
        ?>
        <?php foreach ($players as $i => $p):
          $pos      = $p['position'] ?? ($i + 1);
          $name     = $p['name'] ?? 'Unknown';
          $city     = $p['city'] ?? '';
          $state    = $p['stateprov'] ?? '';
          $country  = $p['country_code'] ?? '';
          $location = implode(', ', array_filter([$city, $state, $country]));
          $points   = $p['wppr_points'] ?? 0;
          $events   = $p['event_count'] ?? '—';
          $isQual   = is_numeric($events) && $events >= 5;
          $isTop3   = $pos <= 3;
          $medals   = ['🥇','🥈','🥉'];
          $rClass   = match((int)$pos) { 1 => 'r1', 2 => 'r2', 3 => 'r3', default => '' };
          $delay    = min($i * 0.025, 0.5);
        ?>
        <div class="row <?= $isTop3 ? 'top-3' : '' ?>"
             style="animation-delay:<?= $delay ?>s"
             data-name="<?= esc(strtolower($name)) ?>"
             data-loc="<?= esc(strtolower($location)) ?>"
             data-qualified="<?= $isQual ? '1' : '0' ?>">
          <div class="rank">
            <span class="rank-num <?= $rClass ?>"><?= (int)$pos ?></span>
            <?php if ($isTop3): ?><span class="medal"><?= $medals[$pos - 1] ?></span><?php endif; ?>
          </div>
          <div class="player">
            <div class="player-name"><?= esc($name) ?></div>
            <?php if ($location): ?><div class="player-meta"><?= esc($location) ?></div><?php endif; ?>
          </div>
          <div class="points"><?= number_format((float)$points, 2) ?></div>
          <div class="events-col">
            <?= esc((string)$events) ?>
            <?php if ($isQual): ?><span class="qualified-check" title="Qualified">&#10003;</span><?php endif; ?>
          </div>
        </div>
        <?php if ($isQual && ++$qualified_count === $cut_after): ?>
        <div class="cut-line">
          <span class="cut-label">CUT LINE &mdash; TOP <?= $cut_after ?></span>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="rankings-footer">
    <span class="rankings-footer-left" id="footer-count">
      <?= isset($total) ? $total : count($players) ?> player<?= ((isset($total) ? $total : count($players)) !== 1) ? 's' : '' ?>
    </span>
    <a class="ifpa-link" href="https://www.ifpapinball.com" target="_blank" rel="noopener">Powered by IFPA &#8599;</a>
  </div>

</div>

<script>
const rows  = Array.from(document.querySelectorAll('#table-body .row'));
const total = rows.length;
let qualOnly = false;

function filterRows() {
  const q = document.getElementById('search').value.toLowerCase().trim();
  let visible = 0;
  rows.forEach(row => {
    const nameMatch = !q || row.dataset.name.includes(q) || row.dataset.loc.includes(q);
    const qualMatch = !qualOnly || row.dataset.qualified === '1';
    const show = nameMatch && qualMatch;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('footer-count').textContent =
    (q || qualOnly) ? `Showing ${visible} of ${total} players` : `${total} player${total !== 1 ? 's' : ''}`;
}

function toggleQualified() {
  qualOnly = !qualOnly;
  document.getElementById('qualified-toggle').classList.toggle('active', qualOnly);
  filterRows();
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
