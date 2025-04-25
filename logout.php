<?php
require_once 'config.php';

header('Content-Type: application/json');

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

echo json_encode(['success' => true]);
?> 