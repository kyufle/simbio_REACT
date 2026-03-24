<?php
// Enviar email de match/interés entre dos usuarios
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreoMatch($email1, $nombre1, $email2, $nombre2, $projectTitle, $projectDesc, $projectImg) {
    require_once __DIR__ . '/logger.php';
    
    $asunto = "¡Nou match a Simbio!";
    
    // No incluir imagen si está vacía
    $imgHtml = '';
    if (!empty($projectImg)) {
        $imgHtml = '<img src="' . htmlspecialchars($projectImg) . '" alt="Projecte" style="max-width: 100%; border-radius: 8px; margin-bottom: 16px;">';
    }
    
    $mensaje = '<!DOCTYPE html>
    <html lang="ca">
    <head><meta charset="UTF-8"><title>Match Simbio</title></head>
    <body style="font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:0;">
    <div style="max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px;">
    <h2 style="color: #2a7ae4;">Coincidència de perfils!</h2>
    <p style="font-size: 16px; color: #333;">Hola <strong>' . htmlspecialchars($nombre1) . '</strong> i <strong>' . htmlspecialchars($nombre2) . '</strong>,</p>
    <p style="font-size: 16px; color: #333;">Heu fet match amb el projecte:</p>
    <div style="text-align: center; margin: 32px 0;">
        ' . $imgHtml . '
        <h3 style="color: #2a7ae4;">' . htmlspecialchars($projectTitle) . '</h3>
        <p style="font-size: 15px; color: #555;">' . htmlspecialchars($projectDesc) . '</p>
    </div>
    <p style="font-size: 15px; color: #888;">Ja podeu contactar i començar a col·laborar!</p>
    <hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">
    <p style="font-size: 13px; color: #aaa;">No responguis a aquest correu. Si tens dubtes, contacta amb el suport de Simbio.</p>
    </div></body></html>';

    $enviados = 0;
    foreach ([[$email1, $nombre1], [$email2, $nombre2]] as [$email, $nombre]) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'davidperera2006@gmail.com';
            $mail->Password = 'ifce dvsr tkws iytv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('davidperera2006@gmail.com', 'Simbio');
            $mail->addAddress($email, $nombre);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;
            $mail->AltBody = 'Heu fet match amb el projecte: ' . $projectTitle;
            $mail->SMTPDebug = 0;
            $mail->send();
            $enviados++;
            log_info("Correo de match enviado a {$email}");
        } catch (Exception $e) {
            log_error('Error enviando correo de match a ' . $email . ': ' . $mail->ErrorInfo);
            error_log('Mailer Error (match): ' . $mail->ErrorInfo);
        }
    }
    
    return $enviados === 2;
}
?>