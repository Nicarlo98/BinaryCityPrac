<?php
// Database connection
include ('../config/conn.php');
// Helper function to generate client code

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include ('../config/conn.php');
    if (isset($_POST['unlink_contact'])) {
        $client_id = $_POST['client_id'];
        $contact_id = $_POST['contact_id'];
        $stmt = $pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
        $stmt->execute([$client_id, $contact_id]);
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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        h1,
        h2 {
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 5px;
            margin: 5px 0;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <button onclick="window.location.href='./contact.php'">Back</button>

    <h2>Unlink Client from Contact</h2>
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
        <input type="submit" name="unlink_contact" value="Unlink">
    </form>

</body>