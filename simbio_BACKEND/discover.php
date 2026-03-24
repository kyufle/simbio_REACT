<?php
require_once 'includes/auth.php';
require_once 'includes/logger.php';

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    log_warning("Acceso denegado a discover.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}

log_info("Usuario accedió a discover.php");

// ⭐ Obtener mensaje flash ANTES de limpiar la sesión
$flash = $_SESSION['flash_message'] ?? null;
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descobrir</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=<?php echo time(); ?>" />
</head>
<body class="discover-page">

<!-- ⭐ Contenedor de toasts (importante que esté aquí) -->
<div id="contenedor-toast" class="contenedor-toast"></div>

<header>
    <div class="nav-links">
        <li><a href="discover.php">Descobrir</a></li>
        <li><a href="profile.php">Perfil</a></li>
        <li><a href="messages.php">Converses</a></li>
    </div>
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

<main id="discover-container">
    <p style="color: #333; text-align: center; padding: 20px;">Carregant projectes...</p>
</main>

<script src="js/utils.js?v=<?php echo time(); ?>"></script>

<?php if (isset($flash) && is_array($flash)): ?>
<script>
    (function() {
        const showFlash = function() {
            <?php
            $tipo = $flash['tipo'] ?? 'info';
            $titulo = json_encode($flash['titulo'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP);
            $descripcion = json_encode($flash['descripcion'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP);

            // Determinar qué función llamar según el tipo
            if ($tipo === 'exito') {
                echo "if (typeof window.mostrarExito === 'function') {";
                echo "  window.mostrarExito($titulo, $descripcion);";
                echo "} else {";
                echo "  console.error('mostrarExito no está definido');";
                echo "}";
            } elseif ($tipo === 'error') {
                echo "if (typeof window.mostrarError === 'function') {";
                echo "  window.mostrarError($titulo, $descripcion);";
                echo "} else {";
                echo "  console.error('mostrarError no está definido');";
                echo "}";
            } elseif ($tipo === 'warning') {
                echo "if (typeof window.mostrarAdvertencia === 'function') {";
                echo "  window.mostrarAdvertencia($titulo, $descripcion);";
                echo "} else {";
                echo "  console.error('mostrarAdvertencia no está definido');";
                echo "}";
            } else {
                echo "if (typeof window.mostrarInfo === 'function') {";
                echo "  window.mostrarInfo($titulo, $descripcion);";
                echo "} else {";
                echo "  console.error('mostrarInfo no está definido');";
                echo "}";
            }
            ?>
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', showFlash);
        } else {
            // DOM ya está listo, ejecutar de inmediato
            setTimeout(showFlash, 100);
        }
    })();
</script>
<?php 
    unset($_SESSION['flash_message']); 
endif; 
?>

<!-- Cargar discover.js -->
<script src="js/discover.js?v=<?php echo filemtime('js/discover.js'); ?>"></script>

</body>
</html>