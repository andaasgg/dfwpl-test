<?php
// $active_page — set before including this file
// $page_title  — optional, used in <title>
$_site_name = 'DFW Pinball League';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? esc($page_title) . ' · ' . $_site_name : $_site_name ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<nav class="site-nav">
  <div class="nav-inner">
    <a href="/" class="nav-logo">DFW <span>Pinball</span> League</a>
    <ul class="nav-links">
      <li><a href="/"             <?= ($active_page ?? '') === 'home'      ? 'class="active"' : '' ?>>Home</a></li>
      <li><a href="/events.php"   <?= ($active_page ?? '') === 'events'    ? 'class="active"' : '' ?>>Events</a></li>
      <li><a href="/standings.php"<?= ($active_page ?? '') === 'standings' ? 'class="active"' : '' ?>>Standings</a></li>
      <li><a href="/sponsors.php" <?= ($active_page ?? '') === 'sponsors'  ? 'class="active"' : '' ?>>Sponsors &amp; Links</a></li>
    </ul>
  </div>
</nav>
