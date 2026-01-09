<?php
require 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //log in
    if (isset($_POST['loginSubmit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM customers WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password == $user['Password']) {
                $_SESSION['user_id'] = $user['CustomerID'];
                $_SESSION['user_name'] = $user['FirstName']; 
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
            }
        } else {
            $_SESSION['error'] = "Email not found.";
        }
    }
    // sign up
    if (isset($_POST['signupSubmit'])) {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $sql = "SELECT * FROM customers WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['signupError'] = "Email is already registered.";
        } else {
            $sql = "INSERT INTO customers (FirstName, LastName, Email, Password, Address, Phone) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssss', $firstName, $lastName, $email, $password, $address, $phone);
            if ($stmt->execute()) {
                header("Location: home.php");
                exit();
            } else {
                $_SESSION['signupError'] = "Error creating account. Please try again.";
            }
        }
    }

    exit();
}
?>
