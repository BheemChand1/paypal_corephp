<?php
// store-order.php
session_start();
header('Content-Type: application/json');

// Include DB connection
require_once 'connection.php';

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orderID']) || !isset($data['courseID']) || !isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Extract data
$orderID = $data['orderID'];
$courseID = intval($data['courseID']);
$amount = floatval($data['amount']);
$status = 'COMPLETED'; // You can enhance this if needed
$userID = isset($data['userID']) ? intval($data['userID']) : null;

// Optional additional data (if passed via JS or PayPal SDK)
$payerName = isset($data['payerName']) ? $data['payerName'] : null;
$payerEmail = isset($data['payerEmail']) ? $data['payerEmail'] : null;
$paymentTime = isset($data['paymentTime']) ? $data['paymentTime'] : date('Y-m-d H:i:s');

try {
    // Prepare and insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_id, course_id, amount, status, payer_name, payer_email, payment_time, user_id)
                            VALUES (:order_id, :course_id, :amount, :status, :payer_name, :payer_email, :payment_time, :user_id)");
    $stmt->execute([
        ':order_id' => $orderID,
        ':course_id' => $courseID,
        ':amount' => $amount,
        ':status' => $status,
        ':payer_name' => $payerName,
        ':payer_email' => $payerEmail,
        ':payment_time' => $paymentTime,
        ':user_id' => $userID
    ]);

    // Set session variables for successful payment
    $_SESSION['payment_success'] = true;
    $_SESSION['order_id'] = $orderID;
    $_SESSION['course_id'] = $courseID;
    $_SESSION['amount'] = $amount;

    echo json_encode([
        'success' => true,
        'message' => 'Order stored successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'DB Error: ' . $e->getMessage()
    ]);
}
?>