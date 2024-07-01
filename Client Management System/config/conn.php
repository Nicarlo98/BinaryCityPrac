<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=bcity_cms', 'Nicarlo@98', 'Klievizo@98');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(
    PDO::ATTR_DEFAULT_FETCH_MODE,
    PDO::FETCH_ASSOC
);
