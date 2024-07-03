<?php
session_start(); // Start the session

// Display error and success messages
if (isset($_SESSION['error'])) {
    echo '<div class="error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

// Database connection
include ('../config/conn.php');

// Fetch clients
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(cc.contact_id) as contact_count 
                         FROM clients c 
                         LEFT JOIN client_contact cc ON c.id = cc.client_id 
                         GROUP BY c.id 
                         ORDER BY c.name ASC");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error fetching clients: " . $e->getMessage();
    $clients = [];
}

// Fetch contacts
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(cc.client_id) as client_count 
                         FROM contacts c 
                         LEFT JOIN client_contact cc ON c.id = cc.contact_id 
                         GROUP BY c.id 
                         ORDER BY c.surname, c.name ASC");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error fetching contacts: " . $e->getMessage();
    $contacts = [];
}

// Handle form submissions for unlinking contacts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_contact'])) {
            $name = $_POST['name'];
            $surname = $_POST['surname'];
            $email = $_POST['email'];

            try {
                // First, check if the email already exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE email = ?");
                $checkStmt->execute([$email]);
                $emailExists = $checkStmt->fetchColumn();

                if ($emailExists) {
                    // Email already exists, set an error message
                    $_SESSION['error'] = "A contact with this email already exists.";
                } else {
                    // Email doesn't exist, proceed with insertion
                    $stmt = $pdo->prepare("INSERT INTO contacts (name, surname, email) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $surname, $email]);
                    $_SESSION['success'] = "Contact created successfully.";
                }
            } catch (PDOException $e) {
                // Handle other potential database errors
                $_SESSION['error'] = "An error occurred: " . $e->getMessage();
            }
        } elseif (isset($_POST['unlink_contact'])) {
            $client_id = $_POST['client_id'];
            $contact_id = $_POST['contact_id'];

            // Check if the link exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM client_contact WHERE client_id = ? AND contact_id = ?");
            $checkStmt->execute([$client_id, $contact_id]);
            $linkExists = $checkStmt->fetchColumn();

            if (!$linkExists) {
                $_SESSION['error'] = "This client and contact are not linked.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM client_contact WHERE client_id = ? AND contact_id = ?");
                $stmt->execute([$client_id, $contact_id]);
                $_SESSION['success'] = "Client and contact successfully unlinked.";
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
    </style>
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
                <!-- Update the button to open the modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#createContactModal">Create Contact</button>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                    data-bs-target="#unLinkContactModal">Unlink Contact</button>
            </div>

            <!-- Create Contact Modal -->
            <div class="modal fade" id="createContactModal" tabindex="-1" aria-labelledby="createContactModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createContactModalLabel">Create Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Include the createContact.php file here -->
                            <h2>Contacts</h2>
                            <form method="post">
                                <input type="text" name="name" placeholder="First Name" required>
                                <input type="text" name="surname" placeholder="Surname" required>
                                <input type="email" name="email" placeholder="Email" required>
                                <input type="submit" name="create_contact" value="Create Contact">
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Unlink Client Modal -->
            <div class="modal fade" id="unLinkContactModal" tabindex="-1" aria-labelledby="unLinkContactModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="unLinkContactModalLabel">Unlink Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Unlink Client from Contact Form -->
                            <h2 class="text-center mb-4">Unlink Client from Contact</h2>
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
                                                <input type="submit" name="unlink_contact" value="Unlink"
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


            <?php if (empty($contacts)): ?>
                <p>No contact(s) found.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Surname</th>
                        <th>Email</th>
                        <th class="text-center">Number of Clients</th>
                    </tr>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td><?= htmlspecialchars($contact['name']) ?></td>
                            <td><?= htmlspecialchars($contact['surname']) ?></td>
                            <td><?= htmlspecialchars($contact['email']) ?></td>
                            <td class="text-center"><?= $contact['client_count'] ?></td>
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