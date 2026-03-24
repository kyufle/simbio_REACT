<?php
// includes/mail.php
// Envío de email de validación con PHPMailer (SMTP)
// Requiere: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreoValidacion($email, $token, $nombre = "") {
	$asunto = "Verifica el teu compte a Simbio";
	$dominio = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$enlace = "https://$dominio/confirm_email.php?validate=" . urlencode($token);
	$mensaje = "<!DOCTYPE html>
	<html lang='ca'>
	<head><meta charset='UTF-8'><title>Verifica el teu compte</title></head>
	<body style=\"background: linear-gradient(135deg, #A3D2CA 0%, #B5EAEA 100%); margin:0; padding:0; font-family: 'Josefin Sans', Arial, sans-serif;\">
	<div style=\"max-width: 500px; margin: 40px auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(163,210,202,0.3); padding: 32px;\">
	<h2 style=\"color: #333; font-family: 'Fjalla One', Arial, sans-serif; text-align:center;\">Benvingut/da a Simbio!</h2>
	<p style=\"font-size: 16px; color: #333;\">Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>
	<p style=\"font-size: 16px; color: #333;\">Gràcies per registrar-te. Per activar el teu compte, fes clic al següent botó:</p>
	<p style=\"text-align: center; margin: 32px 0;\">
	<a href='" . $enlace . "' style=\"background: linear-gradient(135deg, #B5EAEA, #A3D2CA); color: #333; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-size: 18px; display: inline-block; font-weight: 600; box-shadow: 0 4px 15px rgba(163,210,202,0.4);\">Verifica el compte</a>
	</p>
	<p style=\"font-size: 14px; color: #555;\">Aquest enllaç caduca en 48 hores.</p>
	<hr style=\"margin: 32px 0; border: none; border-top: 1px solid #E8E8E8;\">
	<p style=\"font-size: 13px; color: #aaa;\">Si no has sol·licitat aquest registre, ignora aquest missatge.</p>
	</div></body></html>";

	$mail = new PHPMailer(true);
	try {
		// Configuración SMTP (ajusta estos valores)
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'simbiogroup1@gmail.com';
		$mail->Password = 'tiyw alki gwif zukz';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('simbiogroup1@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", para activar tu cuenta visita: $enlace";

		$mail->SMTPDebug = 0; // No mostrar información de depuración en producción
		// $mail->Debugoutput = function($str, $level) { echo "><br>" . htmlspecialchars($str) . "<br>\n"; };
		   $mail->send();
		   return true;
	   } catch (Exception $e) {
		   // Solo registrar el error en el log, no mostrarlo al usuario
		   error_log('Mailer Error: ' . $mail->ErrorInfo);
		   return false;
	}
}



function enviarCorreoCodigoTemporal($email, $codigo, $nombre = "") {
	$asunto = mb_encode_mimeheader("El teu codi temporal d'accés a Simbio", 'UTF-8');
	$mensaje = "<!DOCTYPE html>
	<html lang='ca'>
	<head><meta charset='UTF-8'><title>Codi temporal d'accés</title></head>
	<body style=\"background: linear-gradient(135deg, #A3D2CA 0%, #B5EAEA 100%); margin:0; padding:0; font-family: 'Josefin Sans', Arial, sans-serif;\">
	<div style=\"max-width: 500px; margin: 40px auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(163,210,202,0.3); padding: 32px;\">
	<h2 style=\"color: #333; font-family: 'Fjalla One', Arial, sans-serif; text-align:center;\">Codi temporal d'accés</h2>
	<p style=\"font-size: 16px; color: #333;\">Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>
	<p style=\"font-size: 16px; color: #333;\">Hem rebut una sol·licitud per accedir al teu compte de Simbio. Utilitza el següent codi temporal per iniciar sessió:</p>
	<div style=\"text-align: center; margin: 32px 0;\">
		<span style=\"display: inline-block; font-size: 32px; letter-spacing: 8px; background: #E8E8E8; color: #A3D2CA; padding: 16px 32px; border-radius: 10px; font-weight: bold;\">" . $codigo . "</span>
	</div>
	<p style=\"font-size: 15px; color: #555;\">Aquest codi caduca en 15 minuts. Si no has sol·licitat aquest accés, pots ignorar aquest missatge.</p>
	<hr style=\"margin: 32px 0; border: none; border-top: 1px solid #E8E8E8;\">
	<p style=\"font-size: 13px; color: #aaa;\">No responguis a aquest correu. Si tens dubtes, contacta amb el suport de Simbio.</p>
	</div></body></html>";

	$mail = new PHPMailer(true);
	try {
		// Configuración SMTP (igual que arriba)
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'simbiogroup1@gmail.com';
		$mail->Password = 'tiyw alki gwif zukz';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('simbiogroup1@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", tu código temporal de acceso a Simbio es: $codigo";

		$mail->SMTPDebug = 0;
		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log('Mailer Error: ' . $mail->ErrorInfo);
		return false;
	}
}

function enviarCorreoDigest($email, $nombre, $body) {
	$asunto = mb_encode_mimeheader("Resum diari d'interaccions a Simbio", 'UTF-8');
	$mensaje = "<!DOCTYPE html>
	<html lang='ca'>
	<head><meta charset='UTF-8'><title>Resum diari</title></head>
	<body style=\"background: linear-gradient(135deg, #A3D2CA 0%, #B5EAEA 100%); margin:0; padding:0; font-family: 'Josefin Sans', Arial, sans-serif;\">
	<div style=\"max-width: 600px; margin: 40px auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(163,210,202,0.3); padding: 32px;\">
	<h2 style=\"color: #333; font-family: 'Fjalla One', Arial, sans-serif; text-align:center;\">Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</h2>
	<h3 style=\"color: #A3D2CA; text-align:center; font-weight:600; margin-bottom: 24px;\">Resum diari d'interaccions</h3>
	" . $body . "
	<hr style=\"margin: 32px 0; border: none; border-top: 1px solid #E8E8E8;\">
	<p style=\"font-size: 13px; color: #aaa;\">Aquest és el teu resum diari automàtic de Simbio. No responguis a aquest correu. Si tens dubtes, contacta amb el suport de Simbio.</p>
	</div></body></html>";

	$mail = new PHPMailer(true);
	try {
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'simbiogroup1@gmail.com';
		$mail->Password = 'tiyw alki gwif zukz';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('simbiogroup1@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", aquest és el teu resum diari automàtic de Simbio.";

		$mail->SMTPDebug = 0;
		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log('Mailer Error: ' . $mail->ErrorInfo);
		return false;
	}
}
?>
