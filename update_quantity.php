<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderDetailID = $_POST['orderDetailID'];
    $action = $_POST['action'];

    // Get current quantity from orderdetails
    $sql = "SELECT Quantity FROM orderdetails WHERE OrderDetailID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderDetailID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Order details are missing.";
        exit;
    }

    $row = $result->fetch_assoc();
    $currentQuantity = $row['Quantity'];

    // Determine the new quantity based on the operation (increase or decrease)
    if ($action == 'increase') {
        $newQuantity = $currentQuantity + 1;
    } elseif ($action == 'decrease' && $currentQuantity > 0) {
        $newQuantity = $currentQuantity - 1;
    } else {
        echo "The quantity cannot be reduced below 0.";
        exit;
    }

    // Update quantity in database
    if ($newQuantity > 0) {
        $updateSql = "UPDATE orderdetails SET Quantity = ? WHERE OrderDetailID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $newQuantity, $orderDetailID);
        $updateStmt->execute();
    } else {
        // If quantity is zero, delete the product from the order
        $deleteSql = "DELETE FROM orderdetails WHERE OrderDetailID = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $orderDetailID);
        $deleteStmt->execute();
    }

    header('Location: cart.php');
    exit;
}
?>