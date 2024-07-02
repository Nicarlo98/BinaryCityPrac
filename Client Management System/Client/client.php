<?php
/** * Establishes a connection to the database using the configuration settings in the `config/conn.php` file. */
// Database connection 
include ('../config/conn.php');

// Helper function to generate client code // Fetch clients
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
    <main class="main">
        <aside class="sidebar">
            <nav class="nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li class="active"><a href="client.php">Client</a></li>
                    <li><a href="../Contact/contact.php">Contact</a></li>

                </ul>
            </nav>
        </aside>
    </main>

    <section class="clientview">
        <div class="container">
            <h1>CLIENT</h1>
            <div class="button-group">
                <button class="btn btn-primary" onclick="window.location.href='./createClient.php'">Create
                    Client</button>
                <button class="btn btn-warning" onclick="window.location.href='./linkClient.php'">link
                    Client</button>
            </div>
            <?php if (empty($clients)): ?>
                <p>No client(s) found.</p>
            <?php else: ?>
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
        </div>
    </section>
</body>

</html>