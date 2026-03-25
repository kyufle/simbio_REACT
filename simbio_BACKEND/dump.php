<?php
require_once 'includes/db.php';

$stmt = $conn->query("
    SELECT 
        p.project_id as id,
        p.user_id,
        p.title,
        p.description,
        p.image_path as image,
        p.video_path as video,
        u.entity,
        u.type,
        (SELECT GROUP_CONCAT(t.name) FROM project_tags pt JOIN tag t ON pt.tag_id = t.tag_id WHERE pt.project_id = p.project_id) as tags
    FROM project p
    JOIN user u ON p.user_id = u.user_id
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($projects as &$p) {
    if ($p['tags']) $p['tags'] = explode(',', $p['tags']);
    else $p['tags'] = [];
    if($p['image']) $p['image'] = '/uploads/' . $p['image'];
    if($p['video']) $p['video'] = '/uploads/' . $p['video'];
}
echo json_encode($projects, JSON_PRETTY_PRINT);
