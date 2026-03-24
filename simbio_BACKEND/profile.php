<?php
require_once 'includes/auth.php';
require_once 'includes/bd_profile.php';
require_once 'includes/logger.php';

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    log_warning("Acceso denegado a profile.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}

$email = isset($_SESSION['user']['email']) ? trim($_SESSION['user']['email']) : null;

if (!$email) {
    log_error("Email vacío o no definido en sesión");
    die("Error: Email de sesión no disponible");
}

$profile = getUserProfileByEmail($email);
if (!$profile) {
    // Si no se encuentra el perfil, redirigir o mostrar un error
    log_error("Perfil de usuario no encontrado - Email: " . $email);
    die("Perfil de usuario no encontrado para el email: " . htmlspecialchars($email));
}
log_info("Usuario accedió a profile.php - Email: " . $_SESSION['user']['email']);
$tags = getUserTagsByEmail($email);

// Manejar el guardado del formulario
// Manejar el guardado del formulario

$save_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name         = trim($_POST['name'] ?? $profile['name']);
    $surnames     = trim($_POST['surnames'] ?? $profile['surnames']);
    $entity       = trim($_POST['entity'] ?? $profile['entity']);
    $city         = trim($_POST['city'] ?? $profile['city']);
    $phone_number = trim($_POST['phone_number'] ?? $profile['phone_number']);
    $selected_tags = $_POST['tags'] ?? [];

    if (updateUserProfile($email, $name, $surnames, $entity, $city, $phone_number)) {

        // 1️⃣ ELIMINAR solo las que se han quitado con ❌
        removeUserTags($email, $selected_tags);

        // 2️⃣ AÑADIR las nuevas sin borrar las existentes
        updateUserTags($email, $selected_tags);

        $save_message = 'Perfil actualizado correctamente';

        // Recargar datos
        $profile = getUserProfileByEmail($email);
        $tags    = getUserTagsByEmail($email);

    } else {
        $save_message = 'Error al actualizar el perfil';
    }
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
<body class="profile-page">
    <main>
        <header class="profile-header">
            <nav class="profile-nav">
                <a href="messages.php" class="nav-link">Converses</a>
                <a href="discover.php" class="nav-link">Descobrir</a>
            </nav>
            <h1>Perfil <?php echo htmlspecialchars($profile['name']); ?></h1>
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
        <section class="user-info">
            <!-- <img src="<?php echo htmlspecialchars($profile['image']); ?>" alt="Imatge de perfil" class="profile-image"> -->
            <form id="profile-form" method="POST" class="profile-form">
                <?php if ($save_message): ?>
                    <div class="save-message <?php echo strpos($save_message, 'Error') === false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($save_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nom</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="surnames">Cognoms</label>
                    <input type="text" id="surnames" name="surnames" value="<?php echo htmlspecialchars($profile['surnames']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="entity">Entitat</label>
                    <input type="text" id="entity" name="entity" value="<?php echo htmlspecialchars($profile['entity']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="city">Població</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($profile['city']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Telèfon</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($profile['phone_number']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled readonly>
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
                    <button type="submit" class="btn btn-primary">💾 Guardar Canvis</button>
                    <button type="reset" class="btn btn-secondary">↺ Cancelar</button>
                </div>
            </form>
        </section>
        <!-- Llista de projectes propis -->
        <section class="user-projects">
            <h2>Els meus projectes</h2>
            <a href="new_project.php" class="btn btn-primary">+ Nou projecte</a>
            <div id="user-projects">
                <script>
                    const userEmail = <?php echo json_encode($email); ?>;
                    const allAvailableTags = <?php echo json_encode(getAllAvailableTags()); ?>;
                </script>
                <script src="js/profile.js?v=<?php echo time(); ?>"></script>
                <script src="js/etiquetas.js?v=<?php echo time(); ?>"></script>
            </div>
        </section>
    </main>
</body>
</html>