<?php
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 1;
    
    try {
        $sql = "INSERT INTO productos (nombre, precio, categoria_id, imagen) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $categoria_id, null]);
        
        echo json_encode(['status' => 'success', 'message' => 'Producto guardado']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit(); 
}

try {

    if (isset($_GET['categoria'])) {
        $categoria_id = intval($_GET['categoria']);
        $sql = "SELECT id, nombre, precio FROM productos WHERE categoria_id = :categoria_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['categoria_id' => $categoria_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM productos");
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $productos]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>