<?php
require_once 'db.php';
require_once 'logger.php';

/**
 * Obtiene un proyecto SOLO si pertenece al usuario
 */
function getProjectByIdAndUser(int $project_id, int $user_id): ?array {
    global $conn;

    $stmt = $conn->prepare("
        SELECT *
        FROM project
        WHERE project_id = :id
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $project_id,
        ':user_id'    => $user_id
    ]);

    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        log_warning("Acceso no autorizado al proyecto {$project_id} por user {$user_id}");
        return null;
    }

    return $project;
}

/**
 * Actualiza un proyecto del usuario
 */
function updateProject(
    int $project_id,
    int $user_id,
    string $title,
    string $description
): bool {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE project
        SET title = :title,
            description = :description
        WHERE project_id = :id
          AND user_id = :user_id
    ");

    return $stmt->execute([
        ':title'      => $title,
        ':description'=> $description,
        ':id' => $project_id,
        ':user_id'    => $user_id
    ]);
}

/**
 * Crea un proyecto nuevo
 */
function createProject(int $user_id, string $title, string $description, ?string $image_path, ?string $video_path): ?int {
    global $conn;

    try {
        $stmt = $conn->prepare("
            INSERT INTO project (user_id, title, description, image_path, video_path, deleted)
            VALUES (:user_id, :title, :description, :image_path, :video_path, 0)
        ");
        $stmt->execute([
            ':user_id'     => $user_id,
            ':title'       => $title,
            ':description' => $description,
            ':image_path'  => $image_path,
            ':video_path'  => $video_path
        ]);

        return $conn->lastInsertId();
    } catch (PDOException $e) {
        log_error("Error al crear proyecto para user {$user_id}: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene los tags de un proyecto
 */
function getProjectTags(int $project_id): array {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT name 
            FROM project_tags 
            JOIN tag ON project_tags.tag_id = tag.tag_id 
            WHERE project_id = :project_id
        ");
        $stmt->execute([':project_id' => $project_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        log_error("Error al obtener tags del proyecto {$project_id}: " . $e->getMessage());
        return [];
    }
}

/**
 * Actualiza los tags de un proyecto
 */
function updateProjectTags(int $project_id, array $tags): bool {
    global $conn;
    try {
        // Limpiar tags actuales
        $stmt = $conn->prepare("DELETE FROM project_tags WHERE project_id = :project_id");
        $stmt->execute([':project_id' => $project_id]);

        // Insertar nuevos tags
        foreach ($tags as $tag_name) {
            $stmt = $conn->prepare("SELECT tag_id FROM tag WHERE name = :name");
            $stmt->execute([':name' => $tag_name]);
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tag) {
                $stmt = $conn->prepare("INSERT INTO project_tags (project_id, tag_id) VALUES (:project_id, :tag_id)");
                $stmt->execute([
                    ':project_id' => $project_id,
                    ':tag_id'     => $tag['tag_id']
                ]);
            }
        }

        return true;
    } catch (PDOException $e) {
        log_error("Error al actualizar tags del proyecto {$project_id}: " . $e->getMessage());
        return false;
    }
}