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
require_once __DIR__ . '/includes/mail.php';

$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

$email = trim($input['email'] ?? ($_GET['email'] ?? ''));
$password = $input['password'] ?? '';
$code = trim($input['code'] ?? '');
$resend_code = isset($input['resend_code']) ? $input['resend_code'] : false;
$forgot_step = $_GET['forgot'] ?? null;

// Función auxiliar para responder
function jsonResponse($success, $message, $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));
    exit;
}

// 1. Verificar si ya está logueado
if (!empty($_SESSION['user']) && !isset($input['action'])) {
    jsonResponse(true, 'Ya has iniciado sesión', ['user' => $_SESSION['user']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- LÓGICA DE REENVÍO DE CÓDIGO (forgot=2) ---
    if ($forgot_step == '2' && $resend_code) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();
        $key = md5($email . '|' . $ip);
        
        // Limpiar envíos antiguos en sesión
        if (!isset($_SESSION['code_send_times'])) $_SESSION['code_send_times'] = [];
        $_SESSION['code_send_times'] = array_filter($_SESSION['code_send_times'], function($t) use ($now) { return $t > $now - 900; });

        $send_count = 0;
        foreach ($_SESSION['code_send_times'] as $k => $t) { if ($k === $key) $send_count++; }
        
        if ($send_count >= 5) {
            jsonResponse(false, 'Has superat el límit d\'enviaments. Espera uns minuts.');
        }

        $stmt = $conn->prepare("SELECT user_id, name, is_active FROM user WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonResponse(false, 'No existeix cap compte amb aquest correu electrònic');
        } elseif (!$user['is_active']) {
            jsonResponse(false, 'El compte no està actiu.');
        } else {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', $now + 900);
            
            $stmt2 = $conn->prepare("UPDATE user SET login_code = ?, login_code_expires = ? WHERE user_id = ?");
            $stmt2->execute([$code, $expires, $user['user_id']]);
            
            if (enviarCorreoCodigoTemporal($email, $code, $user['name'])) {
                $_SESSION['code_send_times'][$key] = $now;
                jsonResponse(true, 'Codi reenviat correctament');
            } else {
                jsonResponse(false, 'Error en enviar el correu.');
            }
        }
    }

    // --- LÓGICA DE VALIDAR CÓDIGO (forgot=2) ---
    elseif ($forgot_step == '2') {
        if ($email === '' || $code === '') {
            jsonResponse(false, 'El correu i el codi són obligatoris');
        }

        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['login_code'] !== $code || strtotime($user['login_code_expires']) < time()) {
            jsonResponse(false, 'Codi incorrecte o caducat');
        } else {
            // Login exitoso con código
            $_SESSION['user'] = ['id' => $user['user_id'], 'email' => $user['email'], 'name' => $user['name']];
            $conn->prepare("UPDATE user SET login_code = NULL, login_code_expires = NULL WHERE user_id = ?")->execute([$user['user_id']]);
            jsonResponse(true, 'Benvingut!', ['user' => $_SESSION['user']]);
        }
    }

    // --- LÓGICA DE SOLICITAR CÓDIGO (forgot=1) ---
    elseif ($forgot_step == '1') {
        // ... (Aquí iría tu lógica de generar código y enviarlo, similar al resend)
        // Al final:
        jsonResponse(true, 'T\'hem enviat un codi temporal.');
    }

    // --- LOGIN NORMAL ---
    else {
        if (empty($email) || empty($password)) {
            jsonResponse(false, 'Email i contrasenya obligatoris');
        }

        $result = login($email, $password); // Tu función en auth.php
        if ($result['success']) {
            log_auth('LOGIN', $email, true);
            // Obtenemos datos del usuario tras login exitoso
            $stmt = $conn->prepare("SELECT user_id, email, name FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['user'] = ['id' => $user['user_id'], 'email' => $user['email'], 'name' => $user['name']];
            jsonResponse(true, 'Sessió iniciada', ['user' => $_SESSION['user']]);
        } else {
            log_auth('LOGIN', $email, false, $result['error']);
            jsonResponse(false, $result['error']);
        }
    }
} else {
    jsonResponse(false, 'Método no permitido');
}