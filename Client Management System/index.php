<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Contact Management</title>
    <link rel="stylesheet" href="./assets/style.css" />
    <style>
        /* Add some basic styling to the buttons */
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
    <!-- <?php
    include ('./Includes/sidebar.php');
    ?> -->

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

    <div id="content">
        <nav class="navbar navbar-default">

            <section class="CCM">
                <div class="container">
                    <h1>Client Contact Management</h1>

                    <button onclick="window.location.href='./Client/client.php'">Client</button>
                    <button onclick="window.location.href='./Contact/contact.php'">Contact</button>
                </div>
            </section>
    </div>

</body>

</html>