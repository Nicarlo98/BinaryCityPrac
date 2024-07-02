<?php

session_start();
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
    try {
        // Fetch all existing client codes
        $stmt = $pdo->query("SELECT client_code FROM clients");
        $existingCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Convert name to uppercase and split into words
        $wordArray = preg_split('/\s+/', strtoupper($name), -1, PREG_SPLIT_NO_EMPTY);
        $preLimAlpha = '';

        /**
         * Generates a preliminary alpha code based on the input word array or name.
         *
         * If the word array has 3 or more elements, the first letter of each of the first 3 words is used.
         * If the word array has 2 elements, the first letter of each word is used, followed by 'A'.
         * If the word array has less than 2 elements, the first 3 letters of the name are used.
         * If the name has less than 3 letters, the available letters are used, followed by 'A's to fill the 3-character code.
         **/
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
    include ('../config/conn.php');
    if (isset($_POST['create_client'])) {
        $name = $_POST['name'];
        try {
            // Check if client name already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE name = ?");
            $checkStmt->execute([$name]);
            $nameExists = $checkStmt->fetchColumn();

            if ($nameExists) {
                $_SESSION['error'] = "A client with this name already exists.";
            } else {
                $client_code = generateClientCode($name, $pdo);
                if (!empty($client_code)) {
                    $stmt = $pdo->prepare("INSERT INTO clients (name, client_code) VALUES (?, ?)");
                    $stmt->execute([$name, $client_code]);
                    $_SESSION['success'] = "Client successfully created with code: " . $client_code;
                } else {
                    $_SESSION['error'] = "Failed to generate a unique client code.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
        }
    } elseif (isset($_POST['link_contact'])) {
        try {
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
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $_SESSION['error'] = "An unexpected error occurred: " . $e->getMessage();
        }
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
    <form method="post">
        <input type="text" name="name" placeholder="Client Name" required>
        <input type="submit" name="create_client" value="Create Client">
    </form>
</body>

</html>