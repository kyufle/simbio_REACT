<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/logger.php';
require_once __DIR__ . '/includes/auth.php';

function jsonResponse($success, $message, $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

if (!empty($_SESSION['user'])) {
    jsonResponse(true, 'Usuario logueado', ['user' => $_SESSION['user']]);
}
else {
    jsonResponse(false, 'Usuario no logueado', []);
}

?>
