<?php
// store-order.php

header('Content-Type: application/json');

// Include DB connection
require_once 'connection.php';

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orderID']) || !isset($data['courseID']) || !isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input']);
    exit;
}

// Extract data
$orderID = $data['orderID'];
$courseID = intval($data['courseID']);
$amount = floatval($data['amount']);
$status = 'COMPLETED'; // You can enhance this if needed

// Optional additional data (if passed via JS or PayPal SDK)
$payerName = isset($data['payerName']) ? $data['payerName'] : null;
$payerEmail = isset($data['payerEmail']) ? $data['payerEmail'] : null;
$paymentTime = isset($data['paymentTime']) ? $data['paymentTime'] : date('Y-m-d H:i:s');

try {
    // Prepare and insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_id, course_id, amount, status, payer_name, payer_email, payment_time)
                            VALUES (:order_id, :course_id, :amount, :status, :payer_name, :payer_email, :payment_time)");
    $stmt->execute([
        ':order_id' => $orderID,
        ':course_id' => $courseID,
        ':amount' => $amount,
        ':status' => $status,
        ':payer_name' => $payerName,
        ':payer_email' => $payerEmail,
        ':payment_time' => $paymentTime
    ]);

    echo json_encode(['message' => 'Order stored successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'DB Error: ' . $e->getMessage()]);
}
?>