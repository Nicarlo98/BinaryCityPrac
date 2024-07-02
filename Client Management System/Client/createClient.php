<?php
/**
 * Establishes a connection to the database using the configuration settings in the `config/conn.php` file.
 */
// Database connection
include ('../config/conn.php');
// Helper function to generate client code
function generateClientCode($name, $pdo)
{
    try {
        // Fetch all existing client codes
        $stmt = $pdo->query("SELECT client_code FROM clients");
        $existingCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Convert name to uppercase and split into words
        $wordArray = preg_split('/\s+/', strtoupper($name), -1, PREG_SPLIT_NO_EMPTY);
        $preLimAlpha = '';

        if (count($wordArray) >= 3) {
            for ($i = 0; $i < 3; $i++) {
                $preLimAlpha .= substr($wordArray[$i], 0, 1);
            }
        } elseif (count($wordArray) == 2) {
            $preLimAlpha = substr($wordArray[0], 0, 1) . substr($wordArray[1], 0, 1) . 'A';
        } else {
            $charArray = str_split(strtoupper($name));
            if (count($charArray) >= 3) {
                $preLimAlpha = $charArray[0] . $charArray[1] . $charArray[2];
            } elseif (count($charArray) == 2) {
                $preLimAlpha = $charArray[0] . $charArray[1] . 'A';
            } else {
                $preLimAlpha = $charArray[0] . 'AA';
            }
        }

        // Add numeric part to alpha
        $preLimNum = 1;
        do {
            $preLimString = $preLimAlpha . str_pad($preLimNum, 3, '0', STR_PAD_LEFT);
            $preLimNum++;
        } while (in_array($preLimString, $existingCodes));

        return $preLimString;
    } catch (Exception $ex) {
        // Log the exception or handle it as needed
        return '';
    }
}

// Helper function to check if a client code exists
function clientCodeExists($existingCodes, $code)
{
    return in_array($code, $existingCodes);
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include ('config/conn.php');
    if (isset($_POST['create_client'])) {
        $name = $_POST['name'];
        $client_code = generateClientCode($name, $pdo);
        if (!empty($client_code)) {
            $stmt = $pdo->prepare("INSERT INTO clients (name, client_code) VALUES (?, ?)");
            $stmt->execute([$name, $client_code]);
        } else {
            // Handle the error case
            echo "Failed to generate a unique client code.";
        }
    } elseif (isset($_POST['link_contact'])) {
        $client_id = $_POST['client_id'];
        $contact_id = $_POST['contact_id'];
        $stmt = $pdo->prepare("INSERT INTO client_contact (client_id, contact_id) VALUES (?, ?)");
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
    <button onclick="window.location.href='./client.php'">Back</button>

    <h2>Clients</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Client Name" required>
        <input type="submit" name="create_client" value="Create Client">
    </form>
</body>

</html>