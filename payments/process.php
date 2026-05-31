<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$orderId       = (int)($_POST['order_id'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';
$cardLastFour  = $_POST['card_last_four'] ?? null;

// Validate required fields
if ($orderId <= 0 || $paymentMethod === '') {
    echo json_encode(['success' => false, 'message' => 'Order ID and payment method are required']);
    exit;
}

// Validate payment method
$validMethods = ['Credit Card', 'Debit Card', 'PayPal', 'Bank Transfer'];
if (!in_array($paymentMethod, $validMethods)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
    exit;
}

// Validate card_last_four if provided
if ($cardLastFour !== null && $cardLastFour !== '') {
    $cardLastFour = preg_replace('/[^0-9]/', '', $cardLastFour);
    if (strlen($cardLastFour) !== 4) {
        echo json_encode(['success' => false, 'message' => 'Card last four must be exactly 4 digits']);
        exit;
    }
} else {
    $cardLastFour = null;
}

// Verify order exists and belongs to this user
$stmt = $pdo->prepare("SELECT id, total, currency_code, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Check if payment already exists for this order
$stmt = $pdo->prepare("SELECT id FROM payments WHERE order_id = ? AND status IN ('Completed', 'Pending')");
$stmt->execute([$orderId]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Payment already exists for this order']);
    exit;
}

// Generate unique transaction ID
$transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(8))) . '-' . time();

// Determine status based on payment method
$status = in_array($paymentMethod, ['Credit Card', 'Debit Card']) ? 'Completed' : 'Pending';
if ($paymentMethod === 'PayPal') {
    $status = 'Completed';
}

// Begin transaction
$pdo->beginTransaction();

try {
    // Create payment record
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, user_id, amount, currency_code, payment_method, card_last_four, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $orderId,
        $_SESSION['user_id'],
        $order['total'],
        $order['currency_code'],
        $paymentMethod,
        $cardLastFour,
        $transactionId,
        $status
    ]);
    $paymentId = $pdo->lastInsertId();

    // Update order status based on payment status
    $orderStatus = ($status === 'Completed') ? 'Processing' : 'Pending';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$orderStatus, $orderId]);

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'payment_id'     => $paymentId,
        'transaction_id' => $transactionId,
        'status'         => $status,
        'message'        => 'Payment processed successfully'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Payment processing failed. Please try again.']);
}
