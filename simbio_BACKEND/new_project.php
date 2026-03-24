<?php
require_once 'includes/auth.php';
require_once 'includes/project_service.php';
require_once 'includes/bd_profile.php';
require_once 'includes/logger.php';

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

// Manejo del formulario
$save_message = '';
$errors = [];
$form_title = '';
$form_description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DEBUG: Ver qué llega
    log_warning('DEBUG POST: ' . print_r($_POST, true));
    
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags        = $_POST['tags'] ?? [];

    // Guardar valores para mostrar en el formulario
    $form_title = htmlspecialchars($title);
    $form_description = htmlspecialchars($description);

    // Archivos subidos
    $image_path = $_FILES['image']['name'] ?? null;
    $video_path = $_FILES['video']['name'] ?? null;

    // Validaciones básicas
    if (!$title) {
        $errors[] = "El título es obligatorio";
    }
    if (!$description) {
        $errors[] = "La descripción es obligatoria";
    }

    // Guardar archivos
    if ($image_path) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = uniqid() . "_" . basename($image_path);
        move_uploaded_file($image_tmp, "uploads/" . $image_path);
    }

    if ($video_path) {
        $video_tmp = $_FILES['video']['tmp_name'];
        $video_path = uniqid() . "_" . basename($video_path);
        move_uploaded_file($video_tmp, "uploads/" . $video_path);
    }

    if (empty($errors)) {
        $new_project_id = createProject($user_id, $title, $description, $image_path, $video_path);

        if ($new_project_id) {
            // Asignar tags
            if (!empty($tags)) {
                updateProjectTags($new_project_id, $tags);
            }

            $save_message = "Projecte creat correctament";
            // Limpiar formulario
            $form_title = '';
            $form_description = '';
        } else {
            $errors[] = "Error al crear el projecte";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear nou projecte</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="new-project-page">
    <main>
        <header class="new-project-header">
            <nav class="new-project-nav">
                <a href="chat.php">Converses</a>
                <a href="discover.php">Descobrir</a>
                <a href="profile.php">Perfil</a>
            </nav>
            <h1>Crear nou projecte</h1>
            <div class="session-info">
                <a href="https://youtu.be/zSXbPNl1RJw" target="_blank">Video</a>
                |
                <?php if (isLogged()): ?>
                    <span><?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                    <a href="logout.php">Tancar sessió</a>
                <?php else: ?>
                    <a href="login.php">Iniciar sessió</a>
                <?php endif; ?>
            </div>
        </header>
        <section>
            <form id="profile-form" method="POST" enctype="multipart/form-data" class="profile-form">
                <?php if ($save_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($save_message); ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $err) echo "<p>" . htmlspecialchars($err) . "</p>"; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Titol</label>
                    <input type="text" name="title" id="title" value="<?php echo $form_title; ?>" />
                </div>

                <div class="form-group">
                    <label for="description">Descripció</label>
                    <textarea name="description" id="description"><?php echo $form_description; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Imatge</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="video">Video</label>
                    <input type="file" name="video" id="video" accept="video/*">
                </div>
                <div class="form-section-separator"></div>
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
                    <button type="submit" class="btn btn-primary">Crear Projecte</button>
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