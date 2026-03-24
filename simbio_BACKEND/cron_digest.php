<?php
// cron_digest.php – Digest diario de matches y chats
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mail.php';

// 1. Obtener todos los usuarios activos
$stmt = $conn->query("SELECT user_id, email, name FROM user WHERE is_active = 1");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Calcular fecha de hoy
$today = date('Y-m-d');
$start = $today . ' 00:00:00';
$end = $today . ' 23:59:59';

foreach ($users as $user) {
    $userId = $user['user_id'];
    $email = $user['email'];
    $name = $user['name'];

    // --- Matches de hoy ---
    $stmtMatch = $conn->prepare("SELECT p.title FROM project_like pl JOIN project p ON pl.project_id = p.project_id WHERE pl.user_id = ? AND DATE(pl.created_at) = ?");
    $stmtMatch->execute([$userId, $today]);
    $matches = $stmtMatch->fetchAll(PDO::FETCH_COLUMN);

    // --- Chats de hoy ---
    $stmtChats = $conn->prepare("SELECT DISTINCT u.name, u.user_id FROM message m JOIN user u ON m.user_to_id = u.user_id WHERE m.user_from_id = ? AND m.sent_at BETWEEN ? AND ?");
    $stmtChats->execute([$userId, $start, $end]);
    $chatUsers = $stmtChats->fetchAll(PDO::FETCH_ASSOC);

    $chatSection = "";
    if (count($chatUsers) > 0) {
        foreach ($chatUsers as $chatUser) {
            $stmtMsgs = $conn->prepare("SELECT text FROM message WHERE user_from_id = ? AND user_to_id = ? AND sent_at BETWEEN ? AND ?");
            $stmtMsgs->execute([$userId, $chatUser['user_id'], $start, $end]);
            $msgs = $stmtMsgs->fetchAll(PDO::FETCH_COLUMN);
            $chatSection .= "<b>Avui has parlat amb: " . htmlspecialchars($chatUser['name']) . "</b><br>";
            foreach ($msgs as $msg) {
                $chatSection .= "- " . htmlspecialchars($msg) . "<br>";
            }
        }
    } else {
        $chatSection = "Avui no has tingut converses.";
    }

    // --- Matches section ---
    if (count($matches) > 0) {
        $matchSection = "<b>Matches d'avui:</b><br>" . implode('<br>', array_map('htmlspecialchars', $matches));
    } else {
        $matchSection = "Avui no has tingut cap match.";
    }

    // --- Email body ---
    $body = "<h2>Resum diari d'interaccions</h2><br>" . $matchSection . "<br><br>" . $chatSection;

    // Enviar email
    enviarCorreoDigest($email, $name, $body);
}
?>
