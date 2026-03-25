<?php
$_footer = load_json_assoc(data_path('home.json'));
$_footer_text = trim($_footer['footer_text'] ?? '');
?>
<footer class="site-footer">
  <?php if ($_footer_text): ?>
  <div style="margin-bottom:6px;"><?= esc($_footer_text) ?></div>
  <?php endif; ?>
  <div>
    &copy; <?= date('Y') ?> DFW Pinball League
    &middot; <a href="https://www.ifpapinball.com" target="_blank" rel="noopener">Powered by IFPA</a>
    &middot; <a href="/admin/">Admin</a>
  </div>
</footer>
</body>
</html>
