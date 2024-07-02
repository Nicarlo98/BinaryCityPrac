<?php
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
    include ('../config/conn.php');
    if (isset($_POST['create_contact'])) {
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $stmt = $pdo->prepare("INSERT INTO contacts (name, surname, email) VALUES (?, ?, ?)");
        $stmt->execute([$name, $surname, $email]);
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
    <h2>Contacts</h2>
    <form method="post">
        <input type="text" name="name" placeholder="First Name" required>
        <input type="text" name="surname" placeholder="Surname" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="submit" name="create_contact" value="Create Contact">
    </form>

</body>