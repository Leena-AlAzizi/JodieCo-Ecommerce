<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['productId']) ? intval($_POST['productId']) : 0;

    echo $_POST['productId'];
    echo $_POST['productId'];
    echo $_POST['productId'];
    echo $_POST['productId'];
    echo $_POST['productId'];

    if ($productId <= 0) {
        die("Product ID is required.");
    }

    $conn = new mysqli("localhost", "root", "", "jodieco");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $customerId = $_SESSION['user_id'];

    // تحقق مما إذا كان المنتج موجودًا بالفعل في المفضلة
    $checkStmt = $conn->prepare("SELECT * FROM favorites WHERE CustomerID = ? AND ProductID = ?");
    $checkStmt->bind_param("ii", $customerId, $productId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // حذف المنتج إذا كان موجودًا
        $deleteStmt = $conn->prepare("DELETE FROM favorites WHERE CustomerID = ? AND ProductID = ?");
        $deleteStmt->bind_param("ii", $customerId, $productId);
        if ($deleteStmt->execute()) {
            $_SESSION['message'] = "Product removed successfully.";
        } else {
            $_SESSION['message'] = "Error removing product.";
        }
    } else {
        // إضافة المنتج إلى المفضلة
        $stmt = $conn->prepare("INSERT INTO favorites (CustomerID, ProductID) VALUES (?, ?)");
        $stmt->bind_param("ii", $customerId, $productId);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added successfully.";
        } else {
            $_SESSION['message'] = "Error adding product.";
        }
    }

    $checkStmt->close();
    $conn->close();

    // إعادة توجيه المستخدم
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?>