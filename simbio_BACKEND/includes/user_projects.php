<?php
require_once 'auth.php';
require_once 'bd_profile.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLogged()) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$email = $_SESSION['user']['email'];

$stmt = $conn->prepare("
    SELECT 
        p.project_id,
        p.title,
        p.image_path,
        p.video_path
    FROM project p
    INNER JOIN user u ON u.user_id = p.user_id
    WHERE u.email = ?
      AND p.video_path IS NOT NULL
");

$stmt->execute([$email]);

$projects = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $project) {
    $projects[] = [
        'id'    => $project['project_id'],
        'title' => $project['title'],
        'image' => '/uploads/' . $project['image_path'],
        'video' => '/uploads/' . $project['video_path'],
    ];
}

echo json_encode($projects);
