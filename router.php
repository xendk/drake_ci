<?php
// Snippet from https://drupal.org/files/router-1543858-3.patch
// Also see https://drupal.org/node/1543858

$url = parse_url($_SERVER["REQUEST_URI"]);
if (file_exists('.' . $url['path'])) {
  // Serve the requested resource as-is.
  return FALSE;
}
// Remove opener slash.
$_GET['q'] = substr($url['path'], 1);
include 'index.php';
