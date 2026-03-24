<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

header('Content-Type: application/json; charset=utf-8');

// Comprobar que el admin está logueado
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? null;
$action = $input['action'] ?? null; // 'delete' o 'restore'

if (!$projectId || !$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros']);
    exit;
}

global $conn;

try {
    if ($action === 'delete') {
        // Marcar como eliminado (soft delete)
        $stmt = $conn->prepare("UPDATE project SET deleted = 1 WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        
        log_info("Proyecto ID $projectId marcado como eliminado por admin");
        echo json_encode(['success' => true, 'message' => 'Projecte eliminat correctament']);
        
    } elseif ($action === 'restore') {
        // Restaurar proyecto
        $stmt = $conn->prepare("UPDATE project SET deleted = 0 WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        
        log_info("Proyecto ID $projectId restaurado por admin");
        echo json_encode(['success' => true, 'message' => 'Projecte recuperat correctament']);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acció no vàlida']);
    }
    
} catch (Exception $e) {
    log_error("Error al cambiar estado del proyecto: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al processar la sol·licitud']);
}
