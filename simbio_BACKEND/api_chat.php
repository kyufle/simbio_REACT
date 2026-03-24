<?php
// ==========================
// API Chat - Corregido
// ==========================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/logger.php';

header('Content-Type: application/json');

// ⚠️ Asegurarse de que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario está logueado
if (!isLogged()) {
    log_error("Intento de acceso no autenticado a api_chat.php");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$action = $_GET['action'] ?? '';
$currentUserId = $_SESSION['user']['id'];

try {

    // ==========================
    // ENVIAR MENSAJE
    // ==========================
    if ($action === 'send') {
        $data = json_decode(file_get_contents('php://input'), true);

        $text = trim($data['text'] ?? '');
        $toUserId = (int)($data['to_user_id'] ?? 0);

        if (empty($text) || $toUserId <= 0) {
            log_error("Datos inválidos al enviar mensaje: to_user_id=$toUserId, text_length=" . strlen($text));
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO message (text, user_from_id, user_to_id, sent_at)
            VALUES (:text, :from_id, :to_id, NOW())
        ");

        $result = $stmt->execute([
            ':text' => $text,
            ':from_id' => $currentUserId,
            ':to_id' => $toUserId
        ]);

        if ($result) {
            $messageId = $conn->lastInsertId();
            echo json_encode([
                'success' => true,
                'message_id' => (int)$messageId,
                'text' => htmlspecialchars($text),
                'sent_at' => date('Y-m-d H:i:s'),
                'user_from_id' => $currentUserId
            ]);
        } else {
            log_error("Error al guardar el mensaje en la base de datos");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje']);
        }

    // ==========================
    // OBTENER MENSAJES
    // ==========================
    } elseif ($action === 'get') {
        $otherUserId = (int)($_GET['user_id'] ?? 0);
        $lastMessageId = (int)($_GET['last_message_id'] ?? 0);

        if ($otherUserId <= 0) {
            log_error("user_id inválido al obtener mensajes: user_id=$otherUserId");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id inválido']);
            exit;
        }

        $query = "
            SELECT message_id, text, sent_at, user_from_id, user_to_id
            FROM message
            WHERE (
                (user_from_id = :from1 AND user_to_id = :to1)
                OR
                (user_from_id = :from2 AND user_to_id = :to2)
            )
        ";


        $params = [
            ':from1' => $currentUserId,
            ':to1'   => $otherUserId,
            ':from2' => $otherUserId,
            ':to2'   => $currentUserId
        ];


        if ($lastMessageId > 0) {
            $query .= " AND message_id > :last_id";
            $params[':last_id'] = $lastMessageId;
        }

        $query .= " ORDER BY message_id ASC LIMIT 100";


        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesar y sanitizar mensajes
        $processedMessages = array_map(function($msg) use ($currentUserId) {
            return [
                'message_id' => (int)$msg['message_id'],
                'text' => htmlspecialchars($msg['text']),
                'sent_at' => $msg['sent_at'],
                'user_from_id' => (int)$msg['user_from_id'],
                'user_to_id' => (int)$msg['user_to_id'],
                'isSent' => (int)$msg['user_from_id'] === $currentUserId
            ];
        }, $messages);

        echo json_encode([
            'success' => true,
            'messages' => $processedMessages,
            'count' => count($processedMessages)
        ]);

    // ==========================
    // ACCIÓN INVÁLIDA
    // ==========================
    } else {
        log_error("Acción inválida en api_chat.php: action=$action");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage() // mostrar el mensaje real
    ]);
    log_error("Error en chat API: " . $e->getMessage());
}
