<?php
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');


$stmt = $pdo->query("SELECT * FROM avisos ORDER BY id DESC");
$avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $avisos]);