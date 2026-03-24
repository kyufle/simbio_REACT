<?php
require_once 'db.php';
require_once 'logger.php';

function getUserProfileByEmail($email) {
    global $conn;
    try {
        // Debug: verificar si $conn existe
        if (!$conn) {
            log_error("Conexión a BD no disponible");
            return null;
        }

        $stmt = $conn->prepare("
            SELECT 
                user_id, name, surnames, email, entity, type, 
                phone_number, city, image_path 
            FROM user 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            log_warning("Perfil de usuario no encontrado para email: {$email}");
            return null;
        }

        // Mapear datos del usuario
        $profile = array(
            'user_id'      => $user['user_id'],
            'name'         => $user['name'],
            'surnames'     => $user['surnames'],
            'email'        => $user['email'],
            'entity'       => $user['entity'],
            'type'         => $user['type'],
            'phone_number' => $user['phone_number'],
            'city'         => $user['city'],
            'image'        => "/uploads/" . $user['image_path']
        );

        log_info("Perfil de usuario obtenido por email: {$email}");
        return $profile;

    } catch (PDOException $e) {
        log_error("Error en BD al obtener perfil por email {$email}: " . $e->getMessage());
        return null;
    }
}

function getUserTagsByEmail(string $email): array {
    global $conn;
    $stmt = $conn->prepare("
        SELECT DISTINCT t.name
        FROM user u
        INNER JOIN user_tags ut ON ut.user_id = u.user_id
        INNER JOIN tag t ON t.tag_id = ut.tag_id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getAllAvailableTags(): array {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT name 
            FROM tag 
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        log_error("Error al obtener las etiquetas disponibles: " . $e->getMessage());
        return [];
    }
}

function updateUserProfile($email, $name, $surnames, $entity, $city, $phone_number): bool {
    global $conn;
    try {
        $stmt = $conn->prepare("
            UPDATE user 
            SET name = :name,
                surnames = :surnames,
                entity = :entity,
                city = :city,
                phone_number = :phone_number
            WHERE email = :email
        ");
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surnames', $surnames, PDO::PARAM_STR);
        $stmt->bindParam(':entity', $entity, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        
        if ($result) {
            log_info("Perfil actualizado para usuario: {$email}");
        }
        
        return $result;
    } catch (PDOException $e) {
        log_error("Error al actualizar perfil de usuario {$email}: " . $e->getMessage());
        return false;
    }
}

function updateUserTags($email, $new_tags): bool {
    global $conn;

    try {
        // Obtener user_id
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user_id = $stmt->fetchColumn();

        if (!$user_id) {
            throw new Exception("Usuario no encontrado");
        }

        if (empty($new_tags)) {
            return true;
        }

        // Preparar consultas UNA sola vez (mejor rendimiento)
        $stmtTag = $conn->prepare("SELECT tag_id FROM tag WHERE name = ?");
        $stmtInsert = $conn->prepare("
            INSERT INTO user_tags (user_id, tag_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE tag_id = tag_id
        ");

        foreach ($new_tags as $tag_name) {
            $stmtTag->execute([$tag_name]);
            $tag_id = $stmtTag->fetchColumn();
            if ($tag_id) {
                $stmtInsert->execute([$user_id, $tag_id]);
            }
        }

        return true;

    } catch (Throwable $e) {
        error_log("updateUserTags ERROR: " . $e->getMessage());
        return false;
    }
}

function removeUserTags($email, $tags): bool {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user_id = $stmt->fetchColumn();

        if (!$user_id) return false;

        $stmt = $conn->prepare("SELECT project_id FROM project WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($projects)) return true;

        $placeholdersProjects = implode(',', array_fill(0, count($projects), '?'));
        $params = $projects;

        if (!empty($tags)) {
            $placeholdersTags = implode(',', array_fill(0, count($tags), '?'));
            $params = array_merge($params, $tags);

            $sql = "
                DELETE ut FROM user_tags ut
                JOIN tag t ON t.tag_id = ut.tag_id
                WHERE ut.user_id IN ($placeholdersProjects)
                AND t.name NOT IN ($placeholdersTags)
            ";
        } else {
            $sql = "
                DELETE FROM user_tags
                WHERE user_id IN ($placeholdersProjects)
            ";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return true;

    } catch (Throwable $e) {
        error_log("removeUserTags ERROR: " . $e->getMessage());
        return false;
    }
}