<?php
$host = 'localhost';
$db   = 'instruments_db';
$user = 'root';
$pass = '';
$dns = "mysql:host=$host;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dns, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} 
catch (\PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}
?>
