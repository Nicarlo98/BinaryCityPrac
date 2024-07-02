<?php

session_start();

// Display error and success messages
if (isset($_SESSION['error'])) {
    echo '<div class="error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

/**
 * Establishes a connection to the database using the configuration settings in the `config/conn.php` file.
 */

// Database connection
include ('../config/conn.php');

// Helper function to generate client code
function generateClientCode($name, $pdo)
{
    $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    $code = str_pad($code, 3, 'A');

    $stmt = $pdo->prepare("SELECT MAX(SUBSTRING(client_code, 4)) as max_num FROM clients WHERE client_code LIKE ?");
    $stmt->execute([$code . '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $num = $result['max_num'] ? intval($result['max_num']) + 1 : 1;
    return $code . str_pad($num, 3, '0', STR_PAD_LEFT);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_client'])) {
            $name = $_POST['name'];

            // Check if client name already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE name = ?");
            $checkStmt->execute([$name]);
            $nameExists = $checkStmt->fetchColumn();

            if ($nameExists) {
                $_SESSION['error'] = "A client with this name already exists.";
            } else {
                $client_code = generateClientCode($name, $pdo);
                $stmt = $pdo->prepare("INSERT INTO clients (name, client_code) VALUES (?, ?)");
                $stmt->execute([$name, $client_code]);
                $_SESSION['success'] = "Client successfully created with code: " . $client_code;
            }
        } elseif (isset($_POST['link_contact'])) {
            $client_id = $_POST['client_id'];
            $contact_id = $_POST['contact_id'];

            // Check if the link already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM client_contact WHERE client_id = ? AND contact_id = ?");
            $checkStmt->execute([$client_id, $contact_id]);
            $linkExists = $checkStmt->fetchColumn();

            if ($linkExists) {
                $_SESSION['error'] = "This client and contact are already linked.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO client_contact (client_id, contact_id) VALUES (?, ?)");
                $stmt->execute([$client_id, $contact_id]);
                $_SESSION['success'] = "Client and contact successfully linked.";
            }
        } elseif (isset($_POST['unlink_contact'])) {
            $client_id = $_POST['client_id'];
            $contact_id = $_POST['contact_id'];
            $stmt = $pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
            $stmt->execute([$client_id, $contact_id]);
            $_SESSION['success'] = "Client and contact successfully unlinked.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
// Fetch clients
$stmt = $pdo->query("SELECT c.*, COUNT(cc.contact_id) as contact_count 
                     FROM clients c 
                     LEFT JOIN client_contact cc ON c.id = cc.client_id 
                     GROUP BY c.id 
                     ORDER BY c.name ASC");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch contacts
$stmt = $pdo->query("SELECT c.*, COUNT(cc.client_id) as client_count 
                     FROM contacts c 
                     LEFT JOIN client_contact cc ON c.id = cc.contact_id 
                     GROUP BY c.id 
                     ORDER BY c.surname, c.name ASC");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <h1>Client Contact Management</h1>
    <button onclick="window.location.href='./client.php'">Back</button>

    <h2>Clients</h2>
    <?php if (empty($clients)): ?>
        <p>No client(s) found.</p>
    <?php else: ?>
        <h2>Link Client to Contact</h2>
        <form method="post">
            <select name="client_id">
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="contact_id">
                <?php foreach ($contacts as $contact): ?>
                    <option value="<?= $contact['id'] ?>"><?= htmlspecialchars($contact['surname'] . ', ' . $contact['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="link_contact" value="Link">
        </form>
    <?php endif; ?>
</body>

</html>