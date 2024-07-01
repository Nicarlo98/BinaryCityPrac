<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=bcity_cms', 'Nicarlo@98', 'Klievizo@98');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    if (isset($_POST['link_contact'])) {
        $client_id = $_POST['client_id'];
        $contact_id = $_POST['contact_id'];
        $stmt = $pdo->prepare("INSERT INTO client_contact (client_id, contact_id) VALUES (?, ?)");
        $stmt->execute([$client_id, $contact_id]);
    } elseif (isset($_POST['unlink_contact'])) {
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
    <h1>Client Contact Management</h1>

    <button onclick="window.location.href='./createClient.php'">Create Client</button>
    <button onclick="window.location.href='./createContact.php'">Create Contact</button>

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

</html>