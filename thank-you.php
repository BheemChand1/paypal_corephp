<?php
// Optionally, you can validate if user came from a real payment flow.
session_start();
if (!isset($_SESSION['payment_success'])) {
    header('Location: index.php'); // prevent direct access
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank You for Your Purchase!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CDN (optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .thank-you-box {
            margin-top: 10%;
            text-align: center;
        }

        .thank-you-box .icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .btn-home {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container thank-you-box">
        <div class="icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="text-success">Thank You for Your Purchase!</h2>
        <p class="lead">Your payment was successful. A confirmation email has been sent to you.</p>
        <p>You have successfully enrolled in <strong><?php echo $_GET['course_name']; ?></strong>.</p>
        <a href="index.php" class="btn btn-primary btn-home">Back to Home</a>
    </div>
</body>

</html>