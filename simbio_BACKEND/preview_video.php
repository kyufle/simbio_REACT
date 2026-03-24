<?php
require_once 'includes/auth.php';
require_once 'includes/logger.php';

// Seguridad: solo usuarios autenticados
if (!isLogged()) {
    log_warning("Acceso no autorizado a preview_video.php");
    header('Location: login.php');
    exit;
}

// Obtener el parámetro de la URL
$video = $_GET['video'] ?? null;

// Validación básica
if (!$video || !str_starts_with($video, '/uploads/')) {
    log_error("Ruta de vídeo no válida: " . ($video ?? 'NULL'));
    http_response_code(400);
    die("Vídeo no válido");
}

// Comprobamos que el fichero exista físicamente
$absolutePath = $_SERVER['DOCUMENT_ROOT'] . $video;

if (!file_exists($absolutePath)) {
    log_error("Vídeo no encontrado: $absolutePath");
    http_response_code(404);
    die("Vídeo no encontrado");
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Previsualització del vídeo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #000;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        video {
            max-width: 100%;
            max-height: 100%;
        }
    </style>
</head>
<body>
    <video controls autoplay>
        <source src="<?php echo htmlspecialchars($video); ?>" type="video/mp4">
        El teu navegador no suporta vídeo HTML5.
    </video>
</body>
</html>
