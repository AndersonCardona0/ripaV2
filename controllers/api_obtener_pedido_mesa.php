<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

$mesa_id = isset($_GET['mesa']) ? intval($_GET['mesa']) : 0;

if ($mesa_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mesa no válida.']);
    exit;
}

try {
    // CORREGIDO: Usamos ? en lugar de parámetros con nombre repetidos
    $sql = "SELECT dp.producto_id AS id, prod.nombre, dp.cantidad, dp.precio_unitario AS precio
            FROM detalle_pedidos dp
            INNER JOIN productos prod ON dp.producto_id = prod.id
            INNER JOIN pedidos p ON dp.pedido_id = p.id
            INNER JOIN mesas m ON p.mesa_id = m.id
            WHERE (m.id = ? OR m.numero_mesa = ?) AND p.estado = 'pendiente'";
            
    $stmt = $pdo->prepare($sql);
    // Pasamos el valor dos veces en el array para rellenar ambos '?'
    $stmt->execute([$mesa_id, $mesa_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $items ? $items : []]);

} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>