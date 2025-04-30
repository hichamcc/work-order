<?php

/**
 * Redirect all requests to the public folder
 */

// Path to public directory
$publicPath = __DIR__ . '/public';

// Path to the requested file within the public directory
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Serve the requested file if it exists in the public directory
$requested = $publicPath . $uri;

if ($uri !== '/' && file_exists($requested) && !is_dir($requested)) {
    // If the file exists, return it directly
    return false;
} else {
    // Otherwise, include the index.php from the public directory
    require_once $publicPath . '/index.php';
}