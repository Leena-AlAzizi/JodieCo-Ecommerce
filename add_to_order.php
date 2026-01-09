<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: error_page.php?error=You must be logged in to add items to your bag.");
    exit();
}

$userId = $_SESSION['user_id'];
$productId = isset($_POST['productId']) ? intval($_POST['productId']) : 0;
$productSizeID = isset($_POST['sizeId']) ? intval($_POST['sizeId']) : 0; // معرف الحجم

if ($productId <= 0) {
    header("Location: error_page.php?error=Product ID is required.");
    exit();
}

// تحقق إذا كان هناك طلب سابق في حالة "Before confirmation"
$sqlOrder = "SELECT OrderID FROM orders WHERE CustomerID = ? AND Status = 'Before confirmation'";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $userId);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows > 0) {
    // إذا كان هناك طلب سابق، أضف المنتج إلى الطلب
    $order = $resultOrder->fetch_assoc();
    $orderId = $order['OrderID'];

    // تحقق مما إذا كان المنتج موجودًا بالفعل في الطلب
    $sqlCheck = "SELECT OrderDetailID, Price FROM orderdetails WHERE OrderID = ? AND ProductID = ? AND ProductSizeID = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("iii", $orderId, $productId, $productSizeID);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // إذا كان المنتج موجودًا، قم بتحديث الكمية
        $sqlUpdate = "UPDATE orderdetails SET Quantity = Quantity + 1 WHERE OrderID = ? AND ProductID = ? AND ProductSizeID = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("iii", $orderId, $productId, $productSizeID);
        $stmtUpdate->execute();
    } else {
        //If it does not exist, add the product to the order
        // Retrieve price from ProductSizes
        $sqlPrice = "SELECT Price FROM ProductSizes WHERE ProductSizeID = ?";
        $stmtPrice = $conn->prepare($sqlPrice);
        $stmtPrice->bind_param("i", $productSizeID);
        $stmtPrice->execute();
        $resultPrice = $stmtPrice->get_result();
        $price = 0;

        if ($resultPrice->num_rows > 0) {
            $row = $resultPrice->fetch_assoc();
            $price = $row['Price'];
        }

        $sqlInsert = "INSERT INTO orderdetails (OrderID, ProductID, ProductSizeID, Quantity, Price) VALUES (?, ?, ?, 1, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iiid", $orderId, $productId, $productSizeID, $price);
        $stmtInsert->execute();
    }
} else {
    // If there is no previous order, create a new order
    $sqlInsertOrder = "INSERT INTO orders (CustomerID, Status, TotalPrice) VALUES (?, 'Before confirmation', 0)";
    $stmtInsertOrder = $conn->prepare($sqlInsertOrder);
    $stmtInsertOrder->bind_param("i", $userId);
    $stmtInsertOrder->execute();
    $newOrderId = $stmtInsertOrder->insert_id;

    // Retrieve price from ProductSizes
    $sqlPrice = "SELECT Price FROM ProductSizes WHERE ProductSizeID = ?";
    $stmtPrice = $conn->prepare($sqlPrice);
    $stmtPrice->bind_param("i", $productSizeID);
    $stmtPrice->execute();
    $resultPrice = $stmtPrice->get_result();
    $price = 0;

    if ($resultPrice->num_rows > 0) {
        $row = $resultPrice->fetch_assoc();
        $price = $row['Price'];
    }

    // أضف المنتج إلى الطلب الجديد
    $sqlInsertDetail = "INSERT INTO orderdetails (OrderID, ProductID, ProductSizeID, Quantity, Price) VALUES (?, ?, ?, 1, ?)";
    $stmtInsertDetail = $conn->prepare($sqlInsertDetail);
    $stmtInsertDetail->bind_param("iiid", $newOrderId, $productId, $productSizeID, $price);
    $stmtInsertDetail->execute();
}

// إعادة التوجيه إلى صفحة المنتج
header("Location: 4.php?id=" . $productId);
exit();
?>