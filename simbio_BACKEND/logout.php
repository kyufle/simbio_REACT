<?php
require_once 'includes/auth.php';
require_once 'includes/logger.php';

logout();
header('Location: index.php');
exit;
