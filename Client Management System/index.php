<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Contact Management</title>
    <link rel="stylesheet" href="./assets/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container-fluid {
            background-color: #aaa6ad;
        }

        button {
            background-color: blueviolet;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #ffffff;
            color: blueviolet;
        }
    </style>
</head>

<body>
    <main class="main">
        <aside class="sidebar">
            <nav class="nav">
                <ul>
                    <li class="active"><a href="index.php">Home</a></li>
                    <li><a href="Client/client.php">Client</a></li>
                    <li><a href="Contact/contact.php">Contact</a></li>
                </ul>
            </nav>
        </aside>
    </main>
    <div class="container-fluid">
        <div class="row">


            <div id="content" class="col-md-9 col-lg-10 mx-auto">
                <section class="CCM text-center">
                    <div class="container">
                        <h1>Client Contact Management</h1>

                        <div class="row justify-content-center">
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-purple card-header-icon">
                                        <div class="card-icon">
                                            <i class="fas fa-users" style="color: #ffffff;"></i>
                                        </div>
                                        <p class="card-category" style="color: #2f2f30;">Total Clients</p>
                                        <h3 class="card-title" style="color: #2f2f30;">
                                            <?php
                                            // Database connection
                                            include ('./config/conn.php');
                                            $stmt = $pdo->query("SELECT COUNT(*) AS total_clients FROM clients");
                                            $result = $stmt->fetch();
                                            echo $result['total_clients'];
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-teal card-header-icon">
                                        <div class="card-icon">
                                            <i class="fas fa-users" style="color: #ffffff;"></i>
                                        </div>
                                        <p class="card-category" style="color: #2f2f30;">Total Contacts</p>
                                        <h3 class="card-title" style="color: #2f2f30;">
                                            <?php
                                            $stmt = $pdo->query("SELECT COUNT(*) AS total_contacts FROM contacts");
                                            $result = $stmt->fetch();
                                            echo $result['total_contacts'];
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="card card-stats">
                                    <div class="card-header card-header-indigo card-header-icon">
                                        <div class="card-icon">
                                            <i class="fas fa-link" style="color: #ffffff;"></i>
                                        </div>
                                        <p class="card-category" style="color: #2f2f30;">Total Links</p>
                                        <h3 class="card-title" style="color: #2f2f30;">
                                            <?php
                                            $stmt = $pdo->query("SELECT COUNT(*) AS total_links FROM client_contact");
                                            $result = $stmt->fetch();
                                            echo $result['total_links'];
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>