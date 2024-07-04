<?php
session_start();

// Database connection
include '../config/conn.php';

// Check if the necessary parameters are provided
if (isset($_GET['contact_id']) && isset($_GET['client_id'])) {
    $contact_id = $_GET['contact_id'];
    $client_id = $_GET['client_id'];

    try {
        // Check if the link exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM client_contact WHERE client_id = ? AND contact_id = ?");
        $checkStmt->execute([$client_id, $contact_id]);
        $linkExists = $checkStmt->fetchColumn();

        if (!$linkExists) {
            $_SESSION['error'] = "This client and contact are not linked.";
        } else {
            // Unlink the contact from the client
            $stmt = $pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
            $stmt->execute([$client_id, $contact_id]);
            $_SESSION['success'] = "Client and contact successfully unlinked.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    }

    // Redirect back to the contact page
    header('Location: contact.php');
    exit;
} else {
    // Redirect back to the contact page if the necessary parameters are not provided
    header('Location: contact.php');
    exit;
}