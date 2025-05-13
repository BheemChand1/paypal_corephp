<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coding Courses Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php require_once 'config.php'; ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=USD"></script>

    <style>
        .course-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .rating {
            color: rgb(16, 14, 6);
        }

        .course-image {
            height: 200px;
            object-fit: cover;
        }

        .user-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Coding Courses Dashboard</a>
            <div class="navbar-text text-light">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- <div class="user-info">
            <h4>User Information</h4>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div> -->

        <div class="row">
            <?php
            // Include database connection
            require_once 'connection.php';

            try {
                // Fetch courses from database
                $stmt = $conn->prepare("SELECT * FROM courses");
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Display courses
            
                foreach ($courses as $course) {
                    echo '<div class="col-md-6 col-lg-3">
                        <div class="card course-card">
                            <img src="' . htmlspecialchars($course['image_url']) . '" class="card-img-top course-image" alt="' . htmlspecialchars($course['name']) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($course['name']) . '</h5>
                                <p class="card-text">' . htmlspecialchars($course['description']) . '</p>
                                <div class="rating mb-3">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>' . htmlspecialchars($course['price']) . '</span>
                                </div>
                                <div id="paypal-button-container-' . $course['id'] . '"></div>
                            </div>
                        </div>
                    </div>';
                }


            } catch (PDOException $e) {
                echo '<div class="alert alert-danger" role="alert">
                    Error fetching courses: ' . $e->getMessage() . '
                </div>';
            }
            ?>
        </div>

        <!-- User Orders Section -->
        <div class="mt-5">
            <h2 class="mb-4">Your Purchase History</h2>
            <div class="row">
                <?php
                try {
                    // Fetch user's orders with course details
                    $stmt = $conn->prepare("
                        SELECT o.*, c.name as course_name, c.image_url 
                        FROM orders o 
                        JOIN courses c ON o.course_id = c.id 
                        WHERE o.user_id = :user_id 
                        ORDER BY o.payment_time DESC
                    ");
                    $stmt->execute([':user_id' => $_SESSION['user_id']]);
                    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($orders) > 0) {
                        foreach ($orders as $order) {
                            echo '<div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            <img src="' . htmlspecialchars($order['image_url']) . '" 
                                                class="img-fluid rounded-start h-100" 
                                                style="object-fit: cover;"
                                                alt="' . htmlspecialchars($order['course_name']) . '">
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body">
                                                <h5 class="card-title">' . htmlspecialchars($order['course_name']) . '</h5>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt"></i> ' .
                                date('M d, Y', strtotime($order['payment_time'])) .
                                '</small>
                                                </p>
                                                <p class="card-text">
                                                    <span class="badge bg-success">$' .
                                htmlspecialchars($order['amount']) . '</span>
                                                </p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        Order ID: ' . htmlspecialchars($order['order_id']) .
                                '</small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle"></i> You haven\'t purchased any courses yet.
                            </div>
                        </div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            Error fetching orders: ' . $e->getMessage() . '
                        </div>
                    </div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php foreach ($courses as $course): ?>
                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'blue',
                        shape: 'rect',
                        label: 'pay'
                    },
                    createOrder: function (data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: '<?php echo $course['price']; ?>'
                                },
                                description: '<?php echo addslashes($course["name"]); ?>'
                            }]
                        });
                    },
                    onApprove: function (data, actions) {
                        return actions.order.capture().then(function (details) {
                            const orderData = {
                                orderID: data.orderID,
                                courseID: '<?php echo $course["id"]; ?>',
                                amount: '<?php echo $course["price"]; ?>',
                                payerName: details.payer.name.given_name + ' ' + details.payer.name.surname,
                                payerEmail: details.payer.email_address,
                                paymentTime: details.create_time,
                                userID: '<?php echo $_SESSION["user_id"]; ?>'
                            };

                            // Send to backend
                            fetch('store-order.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(orderData)
                            }).then(res => res.json())
                                .then(res => {
                                    if (res.success) {
                                        alert("Payment successful! Thank you for your purchase.");
                                        window.location.href = "thank-you.php";
                                    } else {
                                        alert("Payment successful but there was an error storing the order. Please contact support.");
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert("There was an error processing your payment. Please try again.");
                                });
                        });
                    },
                    onError: function (err) {
                        console.error('PayPal Error:', err);
                        alert("There was an error with PayPal. Please try again later.");
                    },
                    onCancel: function () {
                        alert("Payment was cancelled. You can try again when you're ready.");
                    }
                }).render('#paypal-button-container-<?php echo $course["id"]; ?>');
            <?php endforeach; ?>
        });
    </script>

</body>

</html>