<?php

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function load_json(string $path): array {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function load_json_assoc(string $path): array {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_json(string $path, mixed $data): bool {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false;
}

function data_path(string $file): string {
    return __DIR__ . '/../data/' . $file;
}

function next_id(array $items): int {
    if (empty($items)) return 1;
    return max(array_column($items, 'id')) + 1;
}

/**
 * Render a small subset of Markdown to HTML safely.
 * HTML is escaped first, so no raw tags can sneak in.
 * Supported: **bold**, *italic*, _italic_, [link text](url)
 */
function render_md(string $text): string {
    $text = esc($text);
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.+?)_/', '<em>$1</em>', $text);
    // Links — URL was already HTML-escaped by esc() above, which is correct for href attributes
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
    return $text;
}

function uploads_path(string $subdir = ''): string {
    return __DIR__ . '/../assets/uploads' . ($subdir ? '/' . ltrim($subdir, '/') : '');
}

/**
 * Handle a file upload. Returns the web-root-relative path on success, or false on failure.
 * $dest_dir should be an absolute filesystem path (use uploads_path()).
 */
function handle_upload(array $file, string $dest_dir): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed, true)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5 MB limit

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safe_ext = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? $ext : 'jpg';
    $filename = bin2hex(random_bytes(8)) . '.' . $safe_ext;
    $dest     = rtrim($dest_dir, '/') . '/' . $filename;

    if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    // Return path relative to web root (assets/uploads/...)
    $root = realpath(__DIR__ . '/..');
    return '/' . ltrim(str_replace($root, '', $dest), '/');
}
