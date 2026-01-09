<?php
require 'db_connection.php';
session_start();

$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productID <= 0) {
    header("Location: error_page.php?error=Product ID is required.");
    exit();
}

$sqlProduct = "
    SELECT 
        p.ProductName, 
        p.ImageURL, 
        p.Description, 
        MIN(ps.Price) AS MinPrice, 
        MAX(ps.Price) AS MaxPrice,
        IF(favorites.CustomerID IS NOT NULL, 1, 0) AS IsFavorite
    FROM 
        Products p
    LEFT JOIN 
        ProductSizes ps 
    ON 
        p.ProductID = ps.ProductID
    LEFT JOIN 
        favorites 
    ON 
        p.ProductID = favorites.ProductID AND favorites.CustomerID = ?
    WHERE 
        p.ProductID = ?
    GROUP BY 
        p.ProductID, p.ProductName, p.ImageURL, p.Description
";

$stmtProduct = $conn->prepare($sqlProduct);
$userID = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 
$stmtProduct->bind_param("ii", $userID, $productID);
$stmtProduct->execute();
$resultProduct = $stmtProduct->get_result();
$product = $resultProduct->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Get available sizes
$sqlSizes = "SELECT ProductSizeID, Size, Price FROM ProductSizes WHERE ProductID = ?";
$stmtSizes = $conn->prepare($sqlSizes);
$stmtSizes->bind_param("i", $productID);
$stmtSizes->execute();
$resultSizes = $stmtSizes->get_result();
$sizes = $resultSizes->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['ProductName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
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
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="d-flex justify-content-center">
                    <img src="<?php echo htmlspecialchars($product['ImageURL']); ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" class="width-80per height-500px img-cover border-radius-10px">
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex flex-column">
                    <label class="font-size-27px mt-5 font-weight-600"><?php echo htmlspecialchars($product['ProductName']); ?></label>
                    <label id="productPrice" class="font-size-17px font-weight-800 mt-3">
                        <?php 
                        if (is_null($product['MinPrice']) || is_null($product['MaxPrice'])) {
                            echo "Price not available";
                        } elseif ($product['MinPrice'] === $product['MaxPrice']) {
                            echo htmlspecialchars($product['MinPrice']) . " JD";
                        } else {
                            echo htmlspecialchars($product['MinPrice']) . " JD - " . htmlspecialchars($product['MaxPrice']) . " JD";
                        }
                        ?>
                    </label>
                    <label class="font-size-14px font-weight-400 mt-2 color-b5b5b5 width-50per"><?php echo htmlspecialchars($product['Description']); ?></label> 
                    <label class="mt-3 font-size-14px">Choose a size:</label>
                    <select class="form-select mt-2 width-50per font-size-12px" id="productSize" name="sizeId">
                        <?php if (!empty($sizes)): ?>
                            <?php foreach ($sizes as $size): ?>
                                <option value="<?php echo htmlspecialchars($size['ProductSizeID']); ?>">
                                    <?php echo htmlspecialchars($size['Size']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>No sizes available</option>
                        <?php endif; ?>
                    </select>
                    <div class="d-flex align-items-center width-50per mt-4">
                        <form action="add_to_order.php" method="POST" style="display: inline;" class="w-100 pe-2">
                            <input type="hidden" name="productId" value="<?php echo $productID; ?>">
                            <input type="hidden" name="sizeId" value="<?php echo htmlspecialchars($sizes[0]['ProductSizeID']); ?>">
                            <button type="submit" class="btn product-btn w-100 me-2">Add To Bag</button>
                        </form>             
                        <form action="add_to_favorites.php" method="POST" style="display: inline;">
                            <input type="hidden" name="productId" value="<?php echo $productID; ?>">
                            <button type="submit" class="border-none background-none favorite-btn">
                                <i class="bi <?php echo $product['IsFavorite'] ? 'bi-heart-fill color-fbbef5' : 'bi-heart color-b5b5b5'; ?> font-size-18px"></i>
                            </button>   
                        </form>
                    </div>
                </div>
            </div>
        </div> 
        <div class="row margin-top-60px mb-5 border-top-b5b5b5 bottom-nav">
            <div class="col-md-7 padding-x-80px margin-top-60px">
                <div>
                    <a href="home.php" class="me-5 page-links">Home</a>
                    <a href="3.php" class="me-5 page-links">About Us</a>
                    <a href="2.php?category=Hair Care" class="me-5 page-links">Hair Care</a>
                </div>
            </div>
            <div class="col-md-5 margin-top-60px d-flex justify-content-center flex-column">
                <img src="img/download.jpg" alt="" class="width-150px">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        const productSizeDropdown = document.getElementById('productSize');
        const sizeInput = document.querySelector('input[name="sizeId"]');

        productSizeDropdown.addEventListener('change', function() {
            sizeInput.value = productSizeDropdown.value; // تحديث قيمة input
        });
    </script>
</body>
</html>