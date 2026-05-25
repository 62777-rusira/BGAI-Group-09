<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Currency code required']);
    exit;
}

// Validate currency exists and is active
$stmt = $pdo->prepare("SELECT code FROM currencies WHERE code = ? AND is_active = 1");
$stmt->execute([$code]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Invalid currency']);
    exit;
}

$_SESSION['currency_code'] = $code;
echo json_encode(['success' => true]);
