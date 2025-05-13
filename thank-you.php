<?php
session_start();

// Check if payment was successful
if (!isset($_SESSION['payment_success']) || $_SESSION['payment_success'] !== true) {
    header("Location: index.php");
    exit();
}

// Get the order details from session
$order_id = $_SESSION['order_id'] ?? '';
$amount = $_SESSION['amount'] ?? '';

// Clear the payment session data
unset($_SESSION['payment_success']);
unset($_SESSION['order_id']);
unset($_SESSION['course_id']);
unset($_SESSION['amount']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Purchase</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Thank You for Your Purchase!</h2>
                        <p class="lead">Your payment has been processed successfully.</p>
                        <p>Order ID: <?php echo htmlspecialchars($order_id); ?></p>
                        <p>Amount Paid: $<?php echo htmlspecialchars($amount); ?></p>
                        <a href="index.php" class="btn btn-primary mt-3">Return to Courses</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>