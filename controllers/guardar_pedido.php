<?php
header('Content-Type: application/json');
require_once '../config/conexion.php';

// Capturamos el flujo de datos JSON que envía JavaScript
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validamos que la información esencial haya llegado
if (!$data || !isset($data['mesa_id']) || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos del pedido incompletos o inválidos.']);
    exit;
}

$mesa_id = intval($data['mesa_id']);
$items = $data['items'];
$usuario_id = 2; // Por ahora dejamos fijo el ID del mesero Carlos que creamos en los inserts

try {
    // Iniciamos una TRANSACCIÓN en MySQL. 
    // Esto asegura que si algo falla a mitad de camino, no se guarde nada a medias.
    $pdo->beginTransaction();

    // 1. Calcular el total real sumando los productos (con el 8% de impuesto)
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['precio']) * intval($item['cantidad']);
    }
    $total_con_impuesto = $subtotal * 1.08;

    // 2. Insertar el pedido general
    $sql_pedido = "INSERT INTO pedidos (mesa_id, usuario_id, estado, total) VALUES (:mesa_id, :usuario_id, 'pendiente', :total)";
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([
        'mesa_id' => $mesa_id,
        'usuario_id' => $usuario_id,
        'total' => $total_con_impuesto
    ]);
    
    // Obtenemos el ID del pedido que se acaba de crear de forma automática
    $pedido_id = $pdo->lastInsertId();

    // 3. Insertar cada ítem en el detalle del pedido
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) 
                    VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)";
    $stmt_detalle = $pdo->prepare($sql_detalle);

    foreach ($items as $item) {
        $stmt_detalle->execute([
            'pedido_id' => $pedido_id,
            'producto_id' => intval($item['id']),
            'cantidad' => intval($item['cantidad']),
            'precio_unitario' => floatval($item['precio'])
        ]);
    }

    // 4. Actualizar el estado de la mesa a 'ocupada'
    $sql_mesa = "UPDATE mesas SET estado = 'ocupada' WHERE numero_mesa = :numero_mesa";
    $stmt_mesa = $pdo->prepare($sql_mesa);
    $stmt_mesa->execute(['numero_mesa' => $mesa_id]);

    // Si todo salió bien, guardamos los cambios definitivamente
    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Pedido enviado a la cocina correctamente.']);

} catch (\PDOException $e) {
    // Si algo falló, revertimos todo para no corromper la base de datos
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>