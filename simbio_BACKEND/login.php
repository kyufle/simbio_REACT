<?php
session_start();

// --- Protección anti-abuso: limitar envíos por IP/email (básico) ---
if (!isset($_SESSION['code_send_times'])) {
    $_SESSION['code_send_times'] = [];
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/logger.php';

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mail.php';

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function validateLoginForm($email, $password)
{
    $errors = [];

    // Validar email
    if ($email === '') {
        $errors['email'] = 'El correu electrònic és obligatori';
    } else {
        if (strpos($email, '@') === false) {
            $errors['email'] = 'El correu electrònic no és vàlid (falta "@")';
        } elseif (strpos($email, ' ') !== false) {
            $errors['email'] = 'El correu electrònic no pot contenir espais';
        } elseif (strlen($email) < 5) {
            $errors['email'] = 'El correu electrònic és massa curt';
        }
    }

    // Validar contrasenya
    if ($password === '') {
        $errors['password'] = 'La contrasenya és obligatòria';
    } else {
        if (strlen($password) < 3) {
            $errors['password'] = 'La contrasenya és massa curta (mínim 3 caràcters)';
        } elseif (strlen($password) > 128) {
            $errors['password'] = 'La contrasenya és massa llarga';
        }
    }

    return $errors;
}

if (isLoggedIn()) {
    header('Location: discover.php');
    exit;
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $errors = [];

    // --- forgot=2: lógica de reenvío de código ---
    if (isset($_GET['forgot']) && $_GET['forgot'] == '2' && isset($_POST['resend_code'])) {
        // Reenviar el código temporal directamente
        $email = trim($_POST['email'] ?? ($_GET['email'] ?? ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();
        $key = md5($email . '|' . $ip);
        // Limpiar envíos antiguos
        $_SESSION['code_send_times'] = array_filter(
            $_SESSION['code_send_times'],
            function($t) use ($now) { return $t > $now - 900; }
        );
        $send_count = 0;
        foreach ($_SESSION['code_send_times'] as $k => $t) {
            if ($k === $key) $send_count++;
        }
        if ($send_count >= 5) {
            $errors['email'] = 'Has superat el límit d\'enviaments. Espera uns minuts.';
        }
        if (empty($errors)) {
            require_once __DIR__ . '/includes/mail.php';
            require_once __DIR__ . '/includes/db.php';
            $stmt = $conn->prepare("SELECT user_id, name, is_active FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $errors['email'] = 'No existeix cap compte amb aquest correu electrònic';
            } elseif (!$user['is_active']) {
                $errors['email'] = 'El compte no està actiu.';
            } else {
                // Limpiar código anterior
                $stmt2 = $conn->prepare("UPDATE user SET login_code = NULL, login_code_expires = NULL WHERE user_id = ?");
                $stmt2->execute([$user['user_id']]);
                // Generar código de 6 dígitos
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', $now + 15 * 60); // 15 minutos desde ahora
                // Guardar código y expiración en BD
                $stmt2 = $conn->prepare("UPDATE user SET login_code = ?, login_code_expires = ? WHERE user_id = ?");
                $stmt2->execute([$code, $expires, $user['user_id']]);
                // Enviar email
                $ok = enviarCorreoCodigoTemporal($email, $code, $user['name']);
                if ($ok) {
                    $_SESSION['code_send_times'][$key] = $now;
                    $_SESSION['flash_message'] = [
                        'tipo' => 'info',
                        'titulo' => 'Codi reenviat',
                        'descripcion' => 'T\'hem tornat a enviar un codi temporal al teu correu.'
                    ];
                    // Mantener en forgot=2
                    header('Location: login.php?forgot=2&email=' . urlencode($email));
                    exit;
                } else {
                    $errors['email'] = 'No s\'ha pogut enviar el correu. Torna-ho a intentar.';
                }
            }
        }
        // IMPORTANTE: NO validar el código si se ha pulsado 'resend_code'
    } elseif (isset($_GET['forgot']) && $_GET['forgot'] == '2') {
        // Validar código temporal
        $code = trim($_POST['code'] ?? '');
        if ($email === '' || $code === '') {
            if ($email === '') $errors['email'] = 'El correu electrònic és obligatori';
            if ($code === '') $errors['code'] = 'El codi és obligatori';
        } else {
            require_once __DIR__ . '/includes/db.php';
            $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $errors['email'] = 'No existeix cap compte amb aquest correu electrònic';
            } elseif (!$user['is_active']) {
                $errors['email'] = 'El compte no està actiu.';
            } elseif (!$user['login_code'] || !$user['login_code_expires']) {
                $errors['code'] = 'No hi ha cap codi actiu per aquest usuari.';
            } elseif ($user['login_code'] !== $code) {
                $errors['code'] = 'El codi no és correcte.';
            } elseif (strtotime($user['login_code_expires']) < time()) {
                $errors['code'] = 'El codi ha caducat.';
            } else {
                // Código correcto: iniciar sesión
                unset($user['password_hash']);
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ];
                // Limpiar el código de la BD
                $stmt2 = $conn->prepare("UPDATE user SET login_code = NULL, login_code_expires = NULL WHERE user_id = ?");
                $stmt2->execute([$user['user_id']]);
                $_SESSION['flash_message'] = [
                    'tipo' => 'exito',
                    'titulo' => 'Benvingut!',
                    'descripcion' => 'Has iniciat sessió correctament amb codi temporal.'
                ];
                header('Location: discover.php');
                exit;
            }
        }
    } elseif (isset($_GET['forgot']) && $_GET['forgot'] == '1') {
        // Validación simple de email
        if ($email === '') {
            $errors['email'] = 'El correu electrònic és obligatori';
        } elseif (strpos($email, '@') === false) {
            $errors['email'] = 'El correu electrònic no és vàlid (falta "@")';
        } elseif (strpos($email, ' ') !== false) {
            $errors['email'] = 'El correu electrònic no pot contenir espais';
        } elseif (strlen($email) < 5) {
            $errors['email'] = 'El correu electrònic és massa curt';
        }

        // Protección anti-abuso: máximo 5 envíos por email/IP cada 15 minutos
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();
        $key = md5($email . '|' . $ip);
        // Limpiar envíos antiguos
        $_SESSION['code_send_times'] = array_filter(
            $_SESSION['code_send_times'],
            function($t) use ($now) { return $t > $now - 900; }
        );
        $send_count = 0;
        foreach ($_SESSION['code_send_times'] as $k => $t) {
            if ($k === $key) $send_count++;
        }
        if ($send_count >= 5) {
            $errors['email'] = 'Has superat el límit d\'enviaments. Espera uns minuts.';
        }
        if (empty($errors)) {
            require_once __DIR__ . '/includes/mail.php';
            require_once __DIR__ . '/includes/db.php';
            // Comprobar si el usuario existe y está activo
            $stmt = $conn->prepare("SELECT user_id, name, is_active FROM user WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $errors['email'] = 'No existeix cap compte amb aquest correu electrònic';
            } elseif (!$user['is_active']) {
                $errors['email'] = 'El compte no està actiu.';
            } else {
                // Limpiar código anterior
                $stmt2 = $conn->prepare("UPDATE user SET login_code = NULL, login_code_expires = NULL WHERE user_id = ?");
                $stmt2->execute([$user['user_id']]);
                // Generar código de 6 dígitos
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = date('Y-m-d H:i:s', $now + 15 * 60); // 15 minutos desde ahora
                // Guardar código y expiración en BD
                $stmt2 = $conn->prepare("UPDATE user SET login_code = ?, login_code_expires = ? WHERE user_id = ?");
                $stmt2->execute([$code, $expires, $user['user_id']]);
                // Enviar email
                $ok = enviarCorreoCodigoTemporal($email, $code, $user['name']);
                if ($ok) {
                    $_SESSION['code_send_times'][$key] = $now;
                    $_SESSION['flash_message'] = [
                        'tipo' => 'info',
                        'titulo' => 'Codi enviat',
                        'descripcion' => 'T\'hem enviat un codi temporal al teu correu. Revisa la safata d\'entrada o correu no desitjat.'
                    ];
                    // Redirigir a formulario de código
                    header('Location: login.php?forgot=2&email=' . urlencode($email));
                    exit;
                } else {
                    $errors['email'] = 'No s\'ha pogut enviar el correu. Torna-ho a intentar.';
                }
            }
        }
    } else {
        // Lógica de login normal
        $password = $_POST['password'] ?? '';
        $errors = validateLoginForm($email, $password);
        if (empty($errors)) {
            $result = login($email, $password);
            if ($result['success']) {
                log_auth('LOGIN', $email, true);
                $_SESSION['flash_message'] = [
                    'tipo' => 'exito',
                    'titulo' => 'Benvingut!',
                    'descripcion' => 'Has iniciat sessió correctament'
                ];
                header('Location: discover.php');
                exit;
            } else {
                log_auth('LOGIN', $email, false, $result['error']);
                if (isset($result['errors']) && is_array($result['errors'])) {
                    $errors = $result['errors'];
                } else {
                    $errors['general'] = $result['error'];
                }
            }
        }
    }

    // Fin del bloque principal de manejo de POST
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de sessió</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <!-- Contenedor para los toasts -->
    <div id="contenedor-toast" class="contenedor-toast"></div>
    
    <main>
        <h1>Iniciar sessió</h1>

        <?php if (!empty($errors)): ?>
            <?php 
            if (isset($errors['general'])): ?>
                <div class="notification error">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>
            <?php foreach ($errors as $field => $error_message): ?>
                <?php if ($field === 'general') continue; ?>
                <?php if (is_array($error_message)): ?>
                    <?php foreach ($error_message as $em): ?>
                        <div class="notification error">
                            <?= htmlspecialchars($em) ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notification error">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($_GET['forgot']) && $_GET['forgot'] == '2'): ?>
            <!-- Formulario para introducir el código temporal -->
            <form method="post" novalidate>
                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? $email) ?>">
                <label>
                    Correu electrònic
                    <input
                        type="email"
                        value="<?= htmlspecialchars($_GET['email'] ?? $email) ?>"
                        disabled
                        maxlength="128">
                </label>
                <label>
                    Codi d'accés
                    <input
                        type="text"
                        name="code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="Introdueix el codi de 6 dígits"
                        autocomplete="one-time-code">
                </label>
                <button type="submit">Validar codi</button>
            </form>
            <form method="post" style="margin-top:1em;">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? $email) ?>">
                <button type="submit" name="resend_code" value="1">Reenviar codi</button>
            </form>
            <div class="button-group">
                <button onclick="window.location.href='login.php'" class="back-btn">Torna a iniciar sessió</button>
            </div>
        <?php elseif (isset($_GET['forgot']) && $_GET['forgot'] == '1'): ?>
            <!-- Formulario para recuperación de contraseña (solo email) -->
            <form method="post" novalidate>
                <label>
                    Correu electrònic
                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="exemple@empresa.cat"
                        maxlength="128"
                        autocomplete="email">
                </label>
                <button type="submit">Envia codi d'accés</button>
            </form>
            <div class="button-group">
                <button onclick="window.location.href='login.php'" class="back-btn">Torna a iniciar sessió</button>
            </div>
        <?php else: ?>
            <!-- Formulario de login normal -->
            <form method="post" novalidate>
                <label>
                    Correu electrònic
                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="exemple@empresa.cat"
                        maxlength="128"
                        autocomplete="email">
                </label>

                <label>
                    Contrasenya
                    <input
                        type="password"
                        name="password"
                        placeholder="********"
                        maxlength="128"
                        autocomplete="current-password">
                </label>

                <button type="submit">Iniciar sessió</button>
            </form>
            <div style="margin-top: 1em;">
                <a href="login.php?forgot=1">No recordes la teva contrasenya?</a>
            </div>
            <div class="button-group">
                <button onclick="window.location.href='register.php'" class="registre-btn">Registrar-se</button>
            </div>
        <?php endif; ?>
    </main>
    <script src="js/utils.js?v=<?php echo time(); ?>"></script>
</body>
</html>