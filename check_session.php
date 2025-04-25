<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = [
    'authenticated' => false,
    'username' => '',
    'email' => ''
];

if (isset($_SESSION['user_id'])) {
    $response['authenticated'] = true;
    $response['username'] = $_SESSION['username'];
    $response['email'] = $_SESSION['email'];
}

echo json_encode($response);
?> 