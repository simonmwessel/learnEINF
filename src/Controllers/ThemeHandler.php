<?php
// Force HTTPS
//if ($_SERVER['HTTPS'] !== 'on') {
//    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//    exit;
//}

// AJAX-Requests exclusive
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(['error' => 'Forbidden']));
}

// CSRF-Token validation
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid CSRF token']));
}

$theme = in_array($_POST['theme'] ?? '', ['light', 'dark']) ? $_POST['theme'] : 'light';

$cookieParams = session_get_cookie_params();
setcookie(
    'theme',
    $theme,
    [
        'expires' => time() + 86400 * 30,
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => $cookieParams['secure'],
        'httponly' => true,
        'samesite' => 'Strict'
    ]
);

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'theme' => $theme,
    'cookie_expires' => date('Y-m-d H:i:s', time() + 86400 * 30)
]);
exit;