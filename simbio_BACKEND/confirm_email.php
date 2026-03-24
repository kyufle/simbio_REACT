<?php
require_once 'includes/mail.php';
require_once 'includes/db.php'; // Incluye tu conexión a la base de datos aquí
require_once 'includes/bd_profile.php';
require_once "includes/logger.php";
    if (isset($_GET['validate'])) {
        $token = $_GET['validate'];
        $stmt = $conn->prepare("SELECT user_id, validation_expires, is_active FROM user WHERE validation_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();
        if ($usuario && !$usuario['is_active'] && $usuario['validation_expires'] > date('Y-m-d H:i:s')) {
            // Activar usuario y eliminar token
            $stmt = $conn->prepare("UPDATE user SET is_active = 1, validation_token = NULL, validation_expires = NULL WHERE user_id = ?");
            $stmt->execute([$usuario['user_id']]);
            log_info("Correu confirmat per l'usuari ID: " . $usuario['user_id']);
        } else {
            // Provocar error 403 real para que Apache lo gestione
            log_warning("Intent de confirmació de correu fallit amb token: " . htmlspecialchars($token));
            http_response_code(403);
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmació de Correu Electrònic</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="confirmation-page">
    <div class="confirmation-box">
        <h1>Correu Confirmat Exitosament</h1>
        <p>El teu correu ha estat confirmat exitosament. Ja pots iniciar sessió.</p>
        <button><a href="login.php">Iniciar Sessió</a></button>
    </div>
</body>
</html>