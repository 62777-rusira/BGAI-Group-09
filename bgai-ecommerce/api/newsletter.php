<?php
// ============================================
// API - Newsletter Subscription
// ============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/app.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server configuration error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = trim($input['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit;
    }

    $db = db();

    // Check if already subscribed
    $exists = $db->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
    $exists->execute([$email]);
    $existing = $exists->fetch();

    if ($existing) {
        if ($existing['is_active']) {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
        } else {
            $db->prepare("UPDATE newsletter_subscribers SET is_active = 1 WHERE id = ?")->execute([$existing['id']]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! Your subscription has been reactivated.']);
        }
        exit;
    }

    // Add new subscriber
    $db->prepare("INSERT INTO newsletter_subscribers (email, is_active) VALUES (?, 1)")->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to BGAI!']);
} catch (Exception $e) {
    error_log("Newsletter API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>
