<?php
$host = 'localhost';
$port = '3306';
$db   = 't2';
$user = 'root';
$pass = 'Victor123.,';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
} catch (\PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
