<?php
define("DBHOST", "localhost");
define("DBNAME", "web1221015_rentCompany");
define("DBUSER", "root");
define("DBPASS", "");

try {
    $pdo = new PDO("mysql:host=" . DBHOST . ";dbname=" . DBNAME, DBUSER, DBPASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
