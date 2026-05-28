<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

// Capturamos la categoría si viene por la URL, si no, por defecto traemos la 1 (Panadería)
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 1;

try {
    $sql = "SELECT id, nombre, precio FROM productos WHERE categoria_id = :categoria_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['categoria_id' => $categoria_id]);
    $productos = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $productos]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>