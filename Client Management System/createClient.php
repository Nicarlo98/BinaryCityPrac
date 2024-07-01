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
    if (isset($_POST['create_client'])) {
        $name = $_POST['name'];
        $client_code = generateClientCode($name, $pdo);
        $stmt = $pdo->prepare("INSERT INTO clients (name, client_code) VALUES (?, ?)");
        $stmt->execute([$name, $client_code]);
    } elseif (isset($_POST['link_contact'])) {
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

    <h2>Clients</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Client Name" required>
        <input type="submit" name="create_client" value="Create Client">
    </form>
</body>

</html>