<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

logoutAdmin();
header('Location: login.php', true, 302);
exit();
