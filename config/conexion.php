<?php

// CORRECCIÓN 1: Ruta segura usando DIRECTORY_SEPARATOR para que concatene correctamente (\ en Windows)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'env.php';

// CORRECCIÓN 2: Agregamos operadores de respaldo para evitar los "Undefined array key" si el .env falla
$host     = $_ENV['DB_HOST'] ?? 'localhost';
$db       = $_ENV['DB_NAME'] ?? '';
$user     = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASSWORD'] ?? '';
$charset  = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Si los datos del .env son correctos, aquí se crea el objeto global $pdo con éxito
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    // Si las credenciales fallan, te dará un mensaje claro en lugar de romper el login de golpe
    die("Error crítico de conexión a la base de datos: " . $e->getMessage());
}
?>