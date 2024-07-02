<?php
/**
 * Establishes a connection to the database using the configuration settings in the `config/conn.php` file.
 */
// Database connection
include ('../config/conn.php');
// Helper function to generate client code

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
    <h1>Client Contact Management</h1>
    <button onclick="window.location.href='../index.php'">Back</button>
    <button onclick="window.location.href='./createClient.php'">Create Client</button>
    <button onclick="window.location.href='./linkClient.php'">Link Client to Contact</button>

    <h2>Clients</h2>
    <?php if (empty($clients)): ?>
        <p>No client(s) found.</p>
    <?php else: ?>
        <h2>Table</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Client Code</th>
                <th>Number of Contacts</th>
            </tr>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['name']) ?></td>
                    <td><?= htmlspecialchars($client['client_code']) ?></td>
                    <td><?= $client['contact_count'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>

</html>