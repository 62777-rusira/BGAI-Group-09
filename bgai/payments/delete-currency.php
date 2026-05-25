<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/payments/currencies.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid currency ID.');
    redirect(BASE_URL . '/payments/currencies.php');
}

// Fetch currency to check if it's AUD
$stmt = $pdo->prepare("SELECT code FROM currencies WHERE id = ?");
$stmt->execute([$id]);
$currency = $stmt->fetch();

if (!$currency) {
    setFlash('danger', 'Currency not found.');
    redirect(BASE_URL . '/payments/currencies.php');
}

if ($currency['code'] === 'AUD') {
    setFlash('danger', 'Cannot delete the base currency (AUD).');
    redirect(BASE_URL . '/payments/currencies.php');
}

// Check if currency is referenced by countries
$stmt = $pdo->prepare("SELECT COUNT(*) FROM countries WHERE currency_code = ?");
$stmt->execute([$currency['code']]);
if ($stmt->fetchColumn() > 0) {
    setFlash('danger', 'Cannot delete this currency. It is used by one or more countries.');
    redirect(BASE_URL . '/payments/currencies.php');
}

$stmt = $pdo->prepare("DELETE FROM currencies WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'Currency deleted successfully.');
redirect(BASE_URL . '/payments/currencies.php');
