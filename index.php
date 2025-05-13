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
    <script
        src="https://www.paypal.com/sdk/js?client-id=AbePEv71gUyQB9-L2B3lXF1UwtV0cHf3zNBYzS_VStTyJ5EEU-QfFqcrDmvalqx-m7bBXbNtArXBGDmE&currency=USD"></script>

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
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php foreach ($courses as $course): ?>
                paypal.Buttons({
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
                                paymentTime: details.create_time
                            };

                            // Send to backend
                            fetch('store-order.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(orderData)
                            }).then(res => res.json())
                                .then(res => {
                                    console.log(res.message);
                                    alert("Payment and order stored successfully!");
                                    // Optionally redirect
                                    window.location.href = "thank-you.php";
                                });
                        });
                    }

                }).render('#paypal-button-container-<?php echo $course["id"]; ?>');
            <?php endforeach; ?>
        });
    </script>

</body>

</html>