<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/payments/countries.php');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid country ID.');
    redirect(BASE_URL . '/payments/countries.php');
}

$stmt = $pdo->prepare("SELECT id, name FROM countries WHERE id = ?");
$stmt->execute([$id]);
$country = $stmt->fetch();

if (!$country) {
    setFlash('danger', 'Country not found.');
    redirect(BASE_URL . '/payments/countries.php');
}

$stmt = $pdo->prepare("DELETE FROM countries WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'Country "' . $country['name'] . '" deleted successfully.');
redirect(BASE_URL . '/payments/countries.php');
