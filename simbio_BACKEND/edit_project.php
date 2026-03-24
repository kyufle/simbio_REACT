<?php
require_once 'includes/auth.php';
require_once 'includes/bd_profile.php';
require_once 'includes/project_service.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['user']['email'];

$profile = getUserProfileByEmail($email);
if (!$profile) {
    http_response_code(403);
    exit('Usuario no válido');
}

$user_id = $profile['user_id'];

$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id) {
    http_response_code(400);
    exit('Proyecto inválido');
}

// 🔒 Seguridad: solo proyectos del usuario
$project = getProjectByIdAndUser($project_id, $user_id);

$tags = getProjectTags($project_id);
$save_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selected_tags = $_POST['tags'] ?? [];

    if (updateProject($project_id, $user_id, $title, $description)) {
        // Actualizar los tags del proyecto
        updateProjectTags($project_id, $selected_tags);
        $save_message = 'Projecte actualitzat correctament';
        $tags = getProjectTags($project_id);
        // Recargar datos
        $project = getProjectByIdAndUser($project_id, $user_id);
    } else {
        $save_message = 'Error al actualitzar el projecte';
    }
}

if (!$project) {
    http_response_code(403);
    exit('No tienes permiso para editar este proyecto');
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil d'Usuari</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="edit-project-page">
    <main>
        <header class="edit-project-header">
            <nav class="edit-project-nav">
                <a href="chat.php" class="nav-link">Converses</a>
                <a href="discover.php" class="nav-link">Descobrir</a>
                <a href="profile.php" class="nav-link">Perfil</a>
            </nav>
            <h1>Edit Project <?php echo htmlspecialchars($project['title']); ?></h1>
            <div class="session-info">
                <a href="https://youtu.be/zSXbPNl1RJw" target="_blank">Video</a>
                |
                <?php if (isLogged()): ?>
                <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <a href="logout.php">Tancar sessió</a>
                <?php else: ?>
                    <a href="login.php">Iniciar sessió</a>
                <?php endif; ?>
            </div>
        </header>
        <section>
            <form method="POST" class="profile-form">
                <?php if ($save_message): ?>
                    <div class="save-message success">
                        <?= htmlspecialchars($save_message) ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Titol</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Descripció</label>
                    <textarea name="description"><?= htmlspecialchars($project['description']) ?></textarea>
                </div>

                <h3>Etiquetes</h3>
                <div class="user-tags-section">
                    <div class="tags-list" id="tags-list">
                        <?php foreach ($tags as $tag): ?>
                            <div class="tag-item">
                                <span><?php echo htmlspecialchars($tag); ?></span>
                                <button type="button" class="remove-tag-btn" data-tag="<?php echo htmlspecialchars($tag); ?>">×</button>
                                <input type="hidden" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <br>
                    <div class="add-tag-container">
                        <div class="tag-search-wrapper">
                            <input type="text" id="tag-search" class="tag-search-input" placeholder="Escriu una etiqueta...">
                            <div class="tag-suggestions" id="tag-suggestions"></div>
                        </div>
                        <button type="button" id="add-tag-btn" class="btn btn-secondary">+ Afegir</button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar canvis</button>
                    <button type="reset" class="btn btn-secondary">↺ Cancelar</button>
                </div>
            </form>
        </section>
    </main>
    <script>
        const allAvailableTags = <?php echo json_encode(getAllAvailableTags()); ?>;
    </script>
    <script src="js/etiquetas.js?v=<?php echo time(); ?>"></script>
</body>
</html>