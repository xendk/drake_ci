<?php

/**
 * @file
 * Router for using with php -s.
 *
 * Adapted from
 * http://stackoverflow.com/questions/11432507/serving-drupal-7-with-built-in-php-5-4-server#11438771
 */
if (preg_match("/\.(engine|inc|info|install|make|module|profile|test|po|sh|.*sql|theme|tpl(\.php)?|xtmpl)/", $_SERVER["REQUEST_URI"])) {
  // File type is not allowed.
  print "Error\n";
}
elseif (preg_match("/(^|\/)\./", $_SERVER["REQUEST_URI"])) {
  // Serve the request as-is.
  return FALSE;
}
elseif (file_exists($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
  return FALSE;
}
else {
  // Feed everything else to Drupal via the "q" GET variable.
  $_GET["q"] = $_SERVER["REQUEST_URI"];
  include "index.php";
}
