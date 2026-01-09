<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "log in to confirm your order";
    exit;
}

$userId = $_SESSION['user_id'];

// تحديث حالة الطلب
$sql = "UPDATE orders SET Status = 'In progress' WHERE CustomerID = ? AND Status = 'Before confirmation'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Order status updated to 'In progress'.";
} else {
    echo "No order found or status already updated.";
}

$stmt->close();
$conn->close();

// إعادة التوجيه إلى صفحة السلة
header("Location: cart.php");
exit;
?>