<?php

session_start();
if (isset($_SESSION['error'])) {
    echo '<div class="error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

// Database connection
include ('../config/conn.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include ('../config/conn.php');
    if (isset($_POST['create_contact'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];

        try {
            // First, check if the email already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
            $checkStmt->execute([$email]);
            $emailExists = $checkStmt->fetchColumn();

            if ($emailExists) {
                // Email already exists, set an error message
                $_SESSION['error'] = "A contact with this email already exists.";
            } else {
                // Email doesn't exist, proceed with insertion
                $stmt = $pdo->prepare("INSERT INTO contacts (name, surname, email) VALUES (?, ?, ?)");
                $stmt->execute([$name, $surname, $email]);
                $_SESSION['success'] = "Contact created successfully.";
            }
        } catch (PDOException $e) {
            // Handle other potential database errors
            $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        }
    }

    // Redirect back to the form page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Contact Management</title>
    <link rel="stylesheet" href="../assets/style.css" />

</head>

<body>

    <button onclick="window.location.href='./contact.php'">Back</button>
    <h2>Contacts</h2>
    <form method="post">
        <input type="text" name="name" placeholder="First Name" required>
        <input type="text" name="surname" placeholder="Surname" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="submit" name="create_contact" value="Create Contact">
    </form>

</body>