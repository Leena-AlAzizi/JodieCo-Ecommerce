<?php
require 'db_connection.php';
session_start();

$customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Query to fetch favorite products
$sqlProducts = "
    SELECT 
        p.ProductID, 
        p.ProductName, 
        p.ImageURL, 
        COUNT(f.FavoriteID) AS TotalFavorites,
        IF(f.CustomerID IS NOT NULL AND f.CustomerID = $customerId, 1, 0) AS IsFavorite
    FROM 
        Products p
    LEFT JOIN 
        Favorites f ON p.ProductID = f.ProductID AND f.CustomerID = $customerId
    GROUP BY 
        p.ProductID
    ORDER BY 
        TotalFavorites DESC, RAND()
    LIMIT 5;
";


$resultProducts = $conn->query($sqlProducts);

$favoriteProducts = [];
if ($resultProducts->num_rows > 0) {
    while ($row = $resultProducts->fetch_assoc()) {
        $favoriteProducts[] = $row;
    }
}

// Query to get prices (MinPrice and MaxPrice) for products
$queryPrices = "
    SELECT 
        p.ProductID, 
        MIN(ps.Price) AS MinPrice, 
        MAX(ps.Price) AS MaxPrice
    FROM 
        Products p
    LEFT JOIN 
        ProductSizes ps 
    ON 
        p.ProductID = ps.ProductID
    GROUP BY 
        p.ProductID;
";

$resultPrices = mysqli_query($conn, $queryPrices);

if ($resultPrices) {
    // Store product prices
    $pricesData = [];
    while ($row = mysqli_fetch_assoc($resultPrices)) {
        $pricesData[$row['ProductID']] = $row; //Store prices using ProductID as key
    }
} else {
    echo "Error in Products Prices Query: " . mysqli_error($conn);
}

$sqlPackages = "
    SELECT 
        PackageID, 
        PackageName, 
        ImageURL, 
        Price
    FROM 
        Packages
    ORDER BY 
        CreatedAt DESC
    LIMIT 3
";

$resultPackages = $conn->query($sqlPackages);

$favoritePackages = [];
if ($resultPackages->num_rows > 0) {
    while ($row = $resultPackages->fetch_assoc()) {
        $favoritePackages[] = $row;
    }
} else {
    echo "No packages found.";
}

$conn->close(); 
?>
<?php

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!--font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

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
        <div class="row margin-top-70px">
          <div class="pe-0 d-flex justify-content-center">
            <img src="img\ad1.png" alt="" style="width: 1450px !important;" class="">
          </div>
        </div>
        <div class="row margin-top-80px">
          <div class="d-flex justify-content-center align-items-center flex-column">
            <label for="">Popular Products</label>
            <label for="" class=" font-size-30px font-weight-600">Trending Now</label>
          </div>
        </div>
        <div class="row px-5 margin-top-25px">
            <?php foreach ($favoriteProducts as $product): ?>
                <div class="card border-none" style="width: 264px; margin: 10px;">
                    <a href="4.php?id=<?php echo $product['ProductID']; ?>">
                        <img src="<?php echo $product['ImageURL']; ?>" class="card-img" alt="<?php echo $product['ProductName']; ?>">
                    </a>
                    <div class="card-body px-0">
                        <div class="d-flex justify-content-between">
                            <a href="4.php?id=<?php echo $product['ProductID']; ?>">
                                <label  class="font-size-15px color-000"><?php echo $product['ProductName']; ?></label>
                            </a>
                            <!-- Add to Favorites Form -->
                            <form action="add_to_favorites.php" method="POST" style="display: inline;">
                                <input type="hidden" name="productId" value="<?php echo $product['ProductID']; ?>">
                                <button type="submit" class="border-none background-none favorite-btn">
                                    <i class="bi <?php echo $product['IsFavorite'] ? 'bi-heart-fill color-fbbef5' : 'bi-heart color-b5b5b5'; ?> font-size-14px"></i>
                                </button>
                            </form>
                        </div>
                        <label for="" class="font-size-14px font-weight-700">
                            <?php
                            $productID = $product['ProductID'];
                            if (isset($pricesData[$productID])) {
                                $minPrice = $pricesData[$productID]['MinPrice'];
                                $maxPrice = $pricesData[$productID]['MaxPrice'];

                                if ($minPrice == $maxPrice) {
                                    echo $minPrice . " JD";
                                } else {
                                    echo $minPrice . " JD - " . $maxPrice . " JD";
                                }
                            } else {
                                echo "Price not available";
                            }
                            ?>
                        </label>
                        <a href="4.php?id=<?php echo $product['ProductID']; ?>" class="btn mt-2">Add To Bag</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-center margin-top-80px" >
            <video id="video1" src="img/jodieco.mp4" autoplay muted loop playsinline class="" style="width: 1450px !important;"></video>
        </div>
        <div class="row margin-top-80px" style="">
            <div class="d-flex justify-content-center align-items-center flex-column">
                <label for="">Popular Packages</label>
                <label for="" class=" font-size-30px font-weight-600">Trending Now</label>
            </div>
        </div>
        <div class="row px-5 margin-top-25px">
            <div class="d-flex justify-content-between">
                <?php foreach ($favoritePackages as $package): ?>
                    <div class="card border-none" style="width: 430px; margin: 10px;">
                        <img src="<?php echo $package['ImageURL']; ?>" class="card-img" alt="<?php echo $package['PackageName']; ?>" style="width: auto !important;">
                        <div class="card-body px-0">
                            <div class="d-flex justify-content-between">
                                <label class="font-size-15px"><?php echo $package['PackageName']; ?></label>
                                <button class="border-none background-none">
                                    <i class="bi bi-heart font-size-14px color-b5b5b5"></i>
                                </button>
                            </div>
                            <label for="" class="font-size-14px font-weight-700">
                                <?php echo $package['Price'] . " JD"; ?>
                            </label>                  
                            <a href="#" class="btn mt-2">Add To Bag</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="row margin-top-60px mb-5 border-top-b5b5b5 bottom-nav">
            <div class="col-md-7 padding-x-80px margin-top-60px">
                <div>
                    <a href="home.php" class="me-5 page-links">Home</a>
                    <a href="3.php" class="me-5 page-links">About Us</a>
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
<script>
    const video = document.getElementById("video1");

    video.addEventListener("mouseover", mouseOver);
    video.addEventListener("mouseout", mouseOut);

    function mouseOver() {
        video.muted = false; 
        video.play(); 
    }

    function mouseOut() {
        video.muted = true; 
    }
</script>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>