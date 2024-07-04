<?php
// Establishes a connection to the database using the configuration settings in the `config/conn.php` file.
include ('../config/conn.php');

// Start the session if it is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function to generate client code
function generateClientCode($name, $pdo)
{
    try {
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

        // Fetch all existing client codes
        $stmt = $pdo->query("SELECT client_code FROM clients");
        $existingCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

// Handle form submissions for linking clients to contacts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Contact Management</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .error {
            color: red;
        }

        .success {
            color: green;
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

        .btn-primary,
        .btn-warning {
            color: #fff;
            background-color: #800080;
            border-color: #800080;
        }

        .text-center {
            text-align: center;
        }
    </style>

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

            <!-- Display error and success messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" role="alert"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <h1>CLIENT</h1>
            <div class="button-group">
                <!-- Update the button to open the modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#createClientModal">Create Client</button>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                    data-bs-target="#linkClientModal">Link Client</button>
            </div>

            <!-- Create Modal -->
            <div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createClientModalLabel">Create Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Include the createClient.php file here -->
                            <form method="post">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" placeholder="Client Name" required
                                        class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="client_code">Client Code</label>
                                    <input type="text" name="client_code" id="client_code"
                                        placeholder="Client Code Gets autoGenerated" readonly class="form-control">
                                </div>
                                <div class="text-center mt-4">
                                    <input type="submit" name="create_client" value="Create Client"
                                        class="btn btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link Modal -->
            <div class="modal fade" id="linkClientModal" tabindex="-1" aria-labelledby="linkClientModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="linkClientModalLabel">Link Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Link Client to Contact Form -->
                            <h2 class="text-center mb-4">Link Client to Contact</h2>
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-md-20">
                                        <form method="post" class="bg-light p-4 rounded">
                                            <div class="form-group">
                                                <label for="client_id">Select Client</label>
                                                <select name="client_id" id="client_id" class="form-control">
                                                    <?php foreach ($clients as $client): ?>
                                                        <option value="<?= htmlspecialchars($client['id']) ?>">
                                                            <?= htmlspecialchars($client['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="contact_id">Select Contact</label>
                                                <select name="contact_id" id="contact_id" class="form-control">
                                                    <?php foreach ($contacts as $contact): ?>
                                                        <option value="<?= htmlspecialchars($contact['id']) ?>">
                                                            <?= htmlspecialchars($contact['surname'] . ', ' . $contact['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="text-center mt-4">
                                                <input type="submit" name="link_contact" value="Link"
                                                    class="btn btn-primary">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($clients)): ?>
                <p>No client(s) found.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Client Code</th>
                        <th class="text-center">Number of Contacts</th>
                    </tr>

                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['name']) ?></td>
                            <td><?= htmlspecialchars($client['client_code']) ?></td>
                            <td class="text-center"><?= $client['contact_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="../assets/main.js"></script>
</body>

</html>