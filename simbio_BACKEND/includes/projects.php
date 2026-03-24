<?php
require_once 'db.php';
require_once 'auth.php';
require_once 'logger.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLogged()) {
    log_warning("Acceso denegado a projects.php: usuario no autenticado");
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

global $conn;

try {
    $userId = $_SESSION['user']['id'];
    log_info("Obteniendo proyectos para usuario: $userId");

    $sql = "
    SELECT 
        p.project_id,
        p.user_id,
        p.title,
        p.description,
        p.image_path,
        p.video_path,
        u.entity,
        u.type,
        COUNT(DISTINCT ut.tag_id) AS tags_en_comun
    FROM project p
    JOIN user u ON p.user_id = u.user_id
    LEFT JOIN project_tags pt ON p.project_id = pt.project_id
    LEFT JOIN user_tags ut 
        ON ut.tag_id = pt.tag_id 
        AND ut.user_id = ?
    WHERE p.user_id != ?
    AND p.deleted = 0
    GROUP BY p.project_id
    ORDER BY tags_en_comun DESC, p.project_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId, $userId]);
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    log_info("Proyectos obtenidos: " . count($projects));

    // Obtener los IDs de proyectos que ya le gustan al usuario
    $stmtUserLikes = $conn->prepare("SELECT project_id FROM project_like WHERE user_id = :user_id");
    $stmtUserLikes->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtUserLikes->execute();
    $userLikes = $stmtUserLikes->fetchAll(PDO::FETCH_COLUMN);

    $allProjects = [];

    foreach ($projects as $project) {
        $projectId = $project['project_id'];
        
        // Obtener nombres de las etiquetas para este proyecto
        $stmtTags = $conn->prepare("
            SELECT t.name 
            FROM project_tags pt 
            JOIN tag t ON pt.tag_id = t.tag_id 
            WHERE pt.project_id = :pid
        ");
        $stmtTags->bindParam(':pid', $projectId, PDO::PARAM_INT);
        $stmtTags->execute();
        $projectTags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);

        $isLiked = in_array($projectId, $userLikes);
        $tagsEnComun = (int)$project['tags_en_comun'];

        $allProjects[] = [
            'id' => $projectId,
            'title' => $project['title'],
            'description' => $project['description'],
            'image' => $project['image_path'] ? "/uploads/" . $project['image_path'] : null,
            'video' => $project['video_path'] ? "/uploads/" . $project['video_path'] : null,
            'entity' => $project['entity'],
            'type' => $project['type'],
            'tags' => $projectTags,
            'match' => $tagsEnComun > 0, // TRUE solo si comparten tags
            'tags_en_comun' => $tagsEnComun,
            'liked' => $isLiked,
            'user_id' => $project['user_id'] // Añadido para enlaces de chat
        ];
    }

    echo json_encode($allProjects, JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    log_error("Error en projects.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error DB: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    log_error("Error general en projects.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}
?>