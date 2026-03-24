<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/logger.php';

// Verificar que el usuario está logueado
if (!isLogged()) {
    log_warning("Acceso no autenticado a chat.php");
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user']['id'];
$otherUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Validar que se proporcione un ID de usuario válido
if (!$otherUserId || $otherUserId === $currentUserId) {
    log_warning("ID de usuario inválido o igual al usuario actual en chat.php: user_id=$otherUserId");
    header('Location: discover.php');
    exit;
}

// Obtener información del otro usuario
try {
    $stmt = $conn->prepare("
        SELECT user_id, name, surnames, entity, type, image_path 
        FROM user 
        WHERE user_id = :user_id 
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $otherUserId]);
    $otherUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otherUser) {
        header('Location: discover.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener usuario: " . $e->getMessage());
}

// Obtener información del usuario actual
try {
    $stmt = $conn->prepare("
        SELECT user_id, name, surnames, entity, type, image_path 
        FROM user 
        WHERE user_id = :user_id 
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener usuario actual: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo htmlspecialchars($otherUser['name'] . ' ' . $otherUser['surnames']); ?></title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="css/chat.css?v="> -->
</head>
<body class="page-chat">
    <div class="chat-container">
        <!-- Header del chat -->
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar">
                    <?php if (!empty($otherUser['image_path'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($otherUser['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($otherUser['name']); ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo substr($otherUser['name'], 0, 1); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="chat-header-text">
                    <h2><?php echo htmlspecialchars($otherUser['name'] . ' ' . $otherUser['surnames']); ?></h2>
                    <p class="chat-entity"><?php echo htmlspecialchars($otherUser['entity'] . ' (' . $otherUser['type'] . ')'); ?></p>
                </div>
            </div>
            <a href="messages.php" class="btn-back">← Tornar</a>
        </div>

        <!-- Contenedor de mensajes -->
        <div class="messages-container" id="messagesContainer">
            <!-- Los mensajes se cargarán aquí vía JavaScript -->
        </div>

        <!-- Área de entrada de mensajes -->
        <div class="chat-input-area">
            <form id="messageForm" class="message-form">
                <input 
                    type="text" 
                    id="messageInput" 
                    class="message-input" 
                    placeholder="Escriu un missatge..."
                    autocomplete="off"
                    required
                />
                <button type="submit" class="btn-send">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.48-.088l-1.22-4.696-4.696-1.22a.75.75 0 0 1-.088-1.48L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 8.431L15.312 4.9z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Datos para JavaScript -->
    <script>
        const currentUserId = <?php echo $currentUserId; ?>;
        const otherUserId = <?php echo $otherUserId; ?>;
        const currentUserName = "<?php echo htmlspecialchars($currentUser['name']); ?>";
        const otherUserName = "<?php echo htmlspecialchars($otherUser['name']); ?>";
        const currentUserImage = "<?php echo htmlspecialchars($currentUser['image_path'] ?? ''); ?>";
        const otherUserImage = "<?php echo htmlspecialchars($otherUser['image_path'] ?? ''); ?>";
    </script>

    <script src="js/chat.js?v=<?php echo time(); ?>"></script>
</body>
</html>
