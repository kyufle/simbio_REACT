<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once 'includes/mail.php';
require_once 'includes/db.php'; 
require_once 'includes/bd_profile.php';


function sendJson($success, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['validate'])) {
    $token = $_GET['validate'];
    $stmt = $conn->prepare("SELECT user_id, validation_expires, is_active FROM user WHERE validation_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    if ($usuario && !$usuario['is_active'] && $usuario['validation_expires'] > date('Y-m-d H:i:s')) {
        $stmt = $conn->prepare("UPDATE user SET is_active = 1, validation_token = NULL, validation_expires = NULL WHERE user_id = ?");
        $stmt->execute([$usuario['user_id']]);
        sendJson(true, "Cuenta verificada correctamente.");
    } else {
        sendJson(false, "Token inválido o expirado.", 403);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    $nombre = trim($input['nombre'] ?? '');
    $apellidos = trim($input['apellidos'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $ciudad = trim($input['ciudad'] ?? '');
    $telefono = trim($input['telefono'] ?? '');
    $entidad = trim($input['entidad'] ?? '');
    $tipo = $input['tipo'] ?? '';
    $tags = $input['tags'] ?? [];

    if (!$nombre || !$apellidos || !$email || !$password || !$ciudad || !$telefono || !$entidad || !$tipo) {
        sendJson(false, "Todos los campos son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJson(false, "El email no es válido.");
    }

    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJson(false, "El email ya está registrado.");
    }

    // Crear usuario
    try {
        $password_hash = hash('sha256', $password);
        $token = hash('sha256', $email . 'simbio1');
        $expires = date('Y-m-d H:i:s', time() + 48 * 60 * 60);

        $sql = "INSERT INTO user (email, password_hash, name, surnames, city, phone_number, entity, type, is_active, validation_token, validation_expires) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email, $password_hash, $nombre, $apellidos, $ciudad, $telefono, $entidad, $tipo, $token, $expires]);

        enviarCorreoValidacion($email, $token, $nombre);
        assignTagsToUserByEmail($email, $tags);
        sendJson(true, "Registro exitoso. Revisa tu correo para validar la cuenta.");

    } catch (Exception $e) {
        sendJson(false, "Error en el servidor: " . $e->getMessage(), 500);
    }
}

function assignTagsToUserByEmail(string $email, array $tags) {
    global $conn;
    if (empty($tags)) return true;
    try {
        $stmtUser = $conn->prepare("SELECT user_id FROM user WHERE email = ? LIMIT 1");
        $stmtUser->execute([$email]);
        $user_id = $stmtUser->fetchColumn();
        if (!$user_id) return false;

        $stmtTag = $conn->prepare("SELECT tag_id FROM tag WHERE name = ?");
        $stmtInsert = $conn->prepare("INSERT INTO user_tags (user_id, tag_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE tag_id = tag_id");

        foreach ($tags as $tag_name) {
            $stmtTag->execute([$tag_name]);
            $tag_id = $stmtTag->fetchColumn();
            if ($tag_id) $stmtInsert->execute([$user_id, $tag_id]);
        }
        return true;
    } catch (Throwable $e) { return false; }
}