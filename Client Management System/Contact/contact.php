<?php
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
    <link rel="stylesheet" href="../assets/style.css" />

</head>

<body>
    <main class="main">
        <aside class="sidebar">
            <nav class="nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../Client/client.php">Client</a></li>
                    <li class="active"><a href="contact.php">Contact</a></li>

                </ul>
            </nav>
        </aside>
    </main>
    <section class="contactview">
        <div class="container">
            <h1>CONTACT</h1>
            <div class="button-group">
                <button class="btn btn-primary" onclick="window.location.href='./createContact.php'">Create
                    Contact</button>
                <button class="btn btn-warning" onclick="window.location.href='./unLinkContact.php'">Unlink
                    Client</button>
            </div>

            <?php if (empty($contacts)): ?>
                <p>No contact(s) found.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th>Number of Clients</th>
                    </tr>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact['name']) ?></td>
                            <td><?= htmlspecialchars($contact['surname']) ?></td>
                            <td><?= htmlspecialchars($contact['email']) ?></td>
                            <td><?= $contact['client_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </section>

</body>