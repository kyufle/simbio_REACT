<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth.php';

// Comprobar que el admin está logueado
if (!isAdminLoggedIn()) {
    log_warning("Acceso denegado a admin/login.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}
log_info("Administrador accedió a admin/index.php - Admin Email: " . $_SESSION['admin_user']['email']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administrador</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
</head>
<body class="login-page-admin">
    <header>
        <div class="header-left">
            <a href="index.php" class="nav-link">Inici</a>
            <a href="users.php" class="nav-link">Usuaris</a>
            <a href="projects.php" class="nav-link">Projectes</a>
            <a href="settings.php" class="nav-link">Configuració</a>
        </div>
        <div class="header-right">
            <a href="https://youtu.be/zSXbPNl1RJw" target="_blank" class="btn-logout">Video</a>
            |
            <?php if (isAdminLoggedIn()): ?>
                <a href="logout.php" class="btn-logout">Tancar sessió</a>
            <?php else: ?>
                <a href="login.php">Iniciar sessió</a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <h1>Hola, <?= htmlspecialchars($_SESSION['admin_user']['name']) ?>!</h1>
        <p style="text-align:center; color:#394867; margin-bottom:2rem;">Benvingut al panell d'administració</p>
        
        <nav>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:1rem;">
                <li><a href="users.php" class="button-link">Gestió d'usuaris</a></li>
                <li><a href="menus.php" class="button-link">Gestió de menús</a></li>
                <li><a href="projects.php" class="button-link">Gestió de projectes</a></li>
                <li><a href="settings.php" class="button-link">Configuració</a></li>
                <li><a href="login.php" class="button-link">Tancar sessió</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>