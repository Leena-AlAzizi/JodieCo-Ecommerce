<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: error_page.php?error=You must be logged in to view your orders.");
    exit();
}

$userId = $_SESSION['user_id'];

// Retrieve orders that are not in "Before confirmation" status
$sqlOrders = "
    SELECT o.OrderID, o.OrderDate, o.Status, o.TotalPrice, GROUP_CONCAT(CONCAT(od.Quantity, ' x ', p.ProductName) ORDER BY od.ProductID SEPARATOR ', ') AS Products
    FROM orders o
    JOIN orderdetails od ON o.OrderID = od.OrderID
    JOIN Products p ON od.ProductID = p.ProductID
    WHERE o.CustomerID = ? AND o.Status != 'Before confirmation'
    GROUP BY o.OrderID, o.OrderDate, o.Status
    ORDER BY o.OrderDate DESC
";

$stmtOrders = $conn->prepare($sqlOrders);
$stmtOrders->bind_param("i", $userId);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <style>
        th{
            border:none;
            padding-top: 20px !important;
            padding-bottom: 20px !important;
            background-color:rgb(225, 225, 225) !important;
            color: #fff;
            font-size:15px;
        }
         td {
            padding-top: 40px !important;
            font-size:13px;
            border: none;
        }

    </style>
</head>
<body>
    <div class="container-fluid px-0">
        <nav class="navbar navbar-expand-lg bg-body-tertiary px-4">
            <div class="container-fluid">
              <img src="img\download.jpg" alt="" class="width-130px pe-4">
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                    <a class="nav-link active color-000 px-3 font-size-18px" aria-current="page" href="home.php">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link color-000 px-3 font-size-18px" href="2.php?category=Hair Care">Hair Care </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link color-000 px-3 font-size-18px" aria-disabled="true" href="2.php?category=Body Care">Body Care</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link color-000 px-3 font-size-18px" aria-disabled="true" href="2.php?category=Face Care">Face Care</a>
                  </li>
                </ul>
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="me-3">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                <?php endif; ?>

                <a href="#" class="nav-link color-000 px-1 font-size-18px d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#loginModal" title="Login">
                    <i class="bi bi-box-arrow-in-right font-size-20px me-2"></i>
                </a>

                <!-- Login Modal -->
                <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content px-4 py-2">
                            <div class="modal-header border-none pb-0">
                                <h5 class="modal-title font-weight-700" id="loginModalLabel">Login</h5>
                                <button type="button" class="border-none background-none" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="loginForm" method="POST" action="auth_handler.php">
                                    <div class="mb-3">
                                        <label for="email" class="form-label font-size-13px">Email</label>
                                        <input type="email" class="form-control font-size-12px" id="email1" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label font-size-13px">Password</label>
                                        <input type="password" class="form-control font-size-12px" id="password1" name="password" required>
                                    </div>
                                    <button type="submit" class="btn w-100 product-btn mt-3" name="loginSubmit">Login</button>
                                </form>
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="text-center mt-2">
                                    <label class="font-size-12px">Don't have an account?</label>
                                    <button type="button" class="font-size-12px border-none background-none font-weight-700" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">
                                        Sign Up
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sign Up Modal -->
                <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content px-4 py-2">
                            <div class="modal-header border-none pb-0">
                                <h5 class="modal-title font-weight-700" id="signupModalLabel">Sign Up</h5>
                                <button type="button" class="border-none background-none" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="signupForm" method="POST" action="auth_handler.php">
                                    <div class="mb-2">
                                        <label for="firstName" class="font-size-13px">First Name</label>
                                        <input type="text" class="form-control font-size-12px" id="firstName" name="firstName" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="lastName" class="font-size-12px">Last Name</label>
                                        <input type="text" class="form-control font-size-12px" id="lastName" name="lastName" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="email" class="font-size-12px">Email</label>
                                        <input type="email" class="form-control font-size-12px" id="email2" name="email" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="password" class="font-size-12px">Password</label>
                                        <input type="password" class="form-control font-size-12px" id="password2" name="password" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="phone" class="font-size-12px">Phone</label>
                                        <input type="phone" class="form-control font-size-12px" id="phone" name="phone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="font-size-12px">Address</label>
                                        <input type="address" class="form-control font-size-12px" id="address" name="address" required>
                                    </div>
                                    <button type="submit" class="btn w-100 product-btn" name="signupSubmit">Sign Up</button>
                                </form>
                                <?php if (isset($_SESSION['signupError'])): ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php echo $_SESSION['signupError']; unset($_SESSION['signupError']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="5.php" class="nav-link color-000 px-1 font-size-18px d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="favorite">
                    <i class="bi bi-person-hearts font-size-20px me-2"></i> 
                </a>
                <a href="cart.php" class="nav-link color-000 px-1 font-size-18px d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="cart">
                    <i class="bi bi-bag-heart font-size-20px me-2"></i> 
                </a>
                <a href="orders.php" class="nav-link color-000 px-1 font-size-18px d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="cart">
                    <i class="bi bi-box2-heart-fill font-size-20px me-2"></i> 
                </a>
              </div>
            </div>
        </nav>
        <div class="row px-5 pt-4">
            <label for="" class="font-size-27px font-weight-600 px-0"><i class="bi bi-box-seam-fill color-fbbef5 font-size-29px me-1"></i> Your Orders</label>
        </div>
        <div class="row px-0 mt-5">
            <table class="table">
                <thead>
                    <tr>
                        <th class="ps-5">Order Date</th>
                        <th>Products</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultOrders->num_rows > 0): ?>
                        <?php while ($order = $resultOrders->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-5"><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['OrderDate']))); ?></td>
                                <td><?php echo htmlspecialchars($order['Products']); ?></td>
                                <td><?php echo htmlspecialchars($order['TotalPrice']); ?> JD</td>
                                <td><?php echo htmlspecialchars($order['Status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="row margin-top-90px mb-5 border-top-b5b5b5 bottom-nav">
            <div class="col-md-7 padding-x-80px margin-top-60px">
                <div>
                    <a href="home.php" class="me-5 page-links">Home</a>
                    <a href="3.html" class="me-5 page-links">About Us</a>
                    <a href="2.php?category=Hair Care" class="me-5 page-links">Hair Care</a>
                    <a href="2.php?category=Face Care" class="me-5 page-links">Face Care</a>
                    <a href="2.php?category=Body Care" class="me-5 page-links">Body Care</a>
                </div>
            </div>
            <div class="col-md-5 margin-top-60px d-flex justify-content-center  flex-column">
                <img src="img\download.jpg" alt="" class="width-150px">
                <div class="mt-3">
                    <a href="https://www.facebook.com/share/1BerNffDUD/?mibextid=LQQJ4d" target="_blank" class="color-d7d7d7 me-4">
                        <i class="bi bi-facebook font-size-25px"></i>
                    </a>
                    <a href="https://www.instagram.com/jodieco.official/profilecard/?igsh=Y2hmYWd3NTVyZTZk" target="_blank" class="color-d7d7d7">
                        <i class="bi bi-instagram font-size-25px"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmtOrders->close();
$conn->close();
?>