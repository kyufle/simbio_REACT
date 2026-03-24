<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'logger.php';
require_once 'mail_match.php';

header('Content-Type: application/json; charset=utf-8');

if(!isLogged()){
    log_warning("Acceso denegado a like_project.php: no autenticado");
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($data['project_id'] ?? 0);
$currentUserId = $_SESSION['user']['id'];

if($projectId <= 0){
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Proyecto inválido']);
    exit;
}

try {
    // 1️⃣ Guardar like (si no existe)
    $stmt = $conn->prepare("
        INSERT IGNORE INTO project_like (user_id, project_id)
        VALUES (:user_id, :project_id)
    ");
    $stmt->execute([':user_id'=>$currentUserId, ':project_id'=>$projectId]);

    // 2️⃣ Obtener propietario del proyecto y datos del proyecto
    $stmt = $conn->prepare("
        SELECT u.user_id, u.name, u.surnames, u.email, p.title, p.description, p.image_path
        FROM project p
        JOIN user u ON p.user_id = u.user_id
        WHERE p.project_id = :pid
        LIMIT 1
    ");
    $stmt->execute([':pid' => $projectId]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        echo json_encode(['success' => false, 'error' => 'Propietario no encontrado']);
        exit;
    }

    // 3️⃣ Detectar MATCH: ¿El usuario tiene las mismas etiquetas que el proyecto?
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT pt.tag_id) AS etiquetas_comunes
        FROM project_tags pt
        INNER JOIN user_tags ut ON pt.tag_id = ut.tag_id
        WHERE pt.project_id = :project_id AND ut.user_id = :user_id
    ");
    $stmt->execute([
        ':project_id' => $projectId,
        ':user_id' => $currentUserId
    ]);
    $etiquetasComunes = (int)$stmt->fetchColumn();
    $match = $etiquetasComunes > 0;

    log_info("Verificación de match: usuario {$currentUserId} dio like a proyecto {$projectId} - Etiquetas en común: {$etiquetasComunes} - Match: " . ($match ? 'SI' : 'NO'));

    // 4️⃣ Si hay match, enviar correos a ambos usuarios
    if ($match) {
        // Obtener datos del usuario actual
        $stmt = $conn->prepare("SELECT email, name, surnames FROM user WHERE user_id = :uid");
        $stmt->execute([':uid' => $currentUserId]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // URL absoluta para la imagen en el correo
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $projectImg = $owner['image_path'] ? "{$protocol}://{$host}/uploads/" . $owner['image_path'] : '';
        
        log_info("Enviando correo de match a {$currentUser['email']} y {$owner['email']}");
        
        // Enviar correo de match
        $resultEmail = enviarCorreoMatch(
            $currentUser['email'], 
            $currentUser['name'] . ' ' . $currentUser['surnames'],
            $owner['email'], 
            $owner['name'] . ' ' . $owner['surnames'],
            $owner['title'], 
            $owner['description'], 
            $projectImg
        );
        
        if ($resultEmail) {
            log_info("Match detectado entre usuario {$currentUserId} y propietario {$owner['user_id']} - Correos enviados exitosamente");
        } else {
            log_error("Match detectado pero falló el envío de correos entre usuario {$currentUserId} y propietario {$owner['user_id']}");
        }
    }

    // Retornamos info para el toast / redirección
    echo json_encode([
        'success' => true,
        'owner_id' => $owner['user_id'],
        'owner_name' => $owner['name'] . ' ' . $owner['surnames'],
        'match' => $match
    ]);

} catch(PDOException $e){
    log_error("Error al registrar like para proyecto {$projectId} por usuario {$currentUserId}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}