<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

$mesa_id = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;

if ($mesa_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mesa no válida.']);
    exit;
}

try {
    // Buscamos si la mesa tiene un pedido pendiente
    $sql = "SELECT dp.producto_id AS id, prod.nombre, dp.cantidad, dp.precio_unitario AS precio
            FROM detalle_pedidos dp
            INNER JOIN productos prod ON dp.producto_id = prod.id
            INNER JOIN pedidos p ON dp.pedido_id = p.id
            INNER JOIN mesas m ON p.mesa_id = m.id
            WHERE m.numero_mesa = :numero_mesa AND p.estado = 'pendiente'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['numero_mesa' => $mesa_id]);
    $items = $stmt->fetchAll();

    // Enviamos los ítems encontrados (si está vacía, enviará un array vacío)
    echo json_encode(['status' => 'success', 'data' => $items]);

} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>