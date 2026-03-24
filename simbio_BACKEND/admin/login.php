<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth.php';

$email = '';
$errors = [];

// Si ya está logueado como admin, redireccionar directamente
// Si ja està loguejat com a administrador, redirecciona directament a la pàgina principal d'admin
// if (isAdminLoggedIn()) {
//     header('Location: index.php');
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if ($email === '') $errors['email'] = 'El correu electrònic és obligatori';
    if ($password === '') $errors['password'] = 'La contrasenya és obligatòria';

    if (empty($errors)) {
        $result = loginAdmin($email, $password);

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'tipo' => 'exito',
                'titulo' => 'Benvingut Admin!',
                'descripcion' => 'Has iniciat sessió correctament'
            ];
            // Es recomendable usar ruta absoluta
            header('Location: index.php');
            // Forzar el envío del header y terminar script
            flush();
            exit;
        } else {
            // Mostrar errores individuales si existen
            if (isset($result['errors']) && is_array($result['errors'])) {
                $errors = array_merge($errors, $result['errors']);
            } else {
                $errors['auth'] = $result['error'] ?? 'Error desconegut';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de sessió Admin</title>
    <link rel="stylesheet" href="../styles.css?v=<?= time() ?>">
</head>

<body class="login-page-admin">
    <main>
        <h1>Iniciar sessió Admin</h1>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="notification error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>Correu electrònic
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="exemple@empresa.cat" maxlength="128" required>
            </label>
            <label>Contrasenya
                <input type="password" name="password" placeholder="********" maxlength="128" required>
            </label>
            <button type="submit">Iniciar sessió</button>
        </form>
    </main>
</body>

</html>