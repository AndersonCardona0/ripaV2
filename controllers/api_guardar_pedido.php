<?php
header('Content-Type: application/json');
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado.']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['mesa_id']) || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos del pedido incompletos o inválidos.']);
    exit;
}

$mesa_id = intval($data['mesa_id']);
$items = $data['items'];
$usuario_id = $_SESSION['usuario_id'];

try {
    $pdo->beginTransaction();

    // 1. Validar y asegurar el ID real de la mesa usando marcadores posicionales (?)
    $sql_mesa_info = "SELECT id FROM mesas WHERE id = ? OR numero_mesa = ? LIMIT 1";
    $stmt_mesa_info = $pdo->prepare($sql_mesa_info);
    $stmt_mesa_info->execute([$mesa_id, $mesa_id]);
    $mesa_row = $stmt_mesa_info->fetch(PDO::FETCH_ASSOC);

    if (!$mesa_row) {
        echo json_encode(['status' => 'error', 'message' => 'Mesa no encontrada.']);
        $pdo->rollBack();
        exit;
    }
    $real_mesa_id = $mesa_row['id'];

    // 2. Calcular el total real sumando los productos (con el 8% de impuesto)
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['precio']) * intval($item['cantidad']);
    }
    $total_con_impuesto = $subtotal * 1.08;

    // 3. Verificar si YA existe un pedido pendiente para esta mesa
    $sql_check = "SELECT id FROM pedidos WHERE mesa_id = ? AND estado = 'pendiente' LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$real_mesa_id]);
    $pedido_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($pedido_existente) {
        // ESCENARIO A: El pedido ya existe, lo actualizamos
        $pedido_id = $pedido_existente['id'];
        
        $sql_update_pedido = "UPDATE pedidos SET total = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update_pedido);
        $stmt_update->execute([$total_con_impuesto, $pedido_id]);
        
        // Limpiamos los detalles viejos de este pedido para reescribirlos
        $sql_delete_detalles = "DELETE FROM detalle_pedidos WHERE pedido_id = ?";
        $stmt_delete = $pdo->prepare($sql_delete_detalles);
        $stmt_delete->execute([$pedido_id]);
    } else {
        // ESCENARIO B: Es un pedido nuevo, lo insertamos
        $sql_pedido = "INSERT INTO pedidos (mesa_id, usuario_id, estado, total) VALUES (?, ?, 'pendiente', ?)";
        $stmt_pedido = $pdo->prepare($sql_pedido);
        $stmt_pedido->execute([$real_mesa_id, $usuario_id, $total_con_impuesto]);
        $pedido_id = $pdo->lastInsertId();
    }

    // 4. Insertar los ítems actualizados en el detalle
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $pdo->prepare($sql_detalle);

    foreach ($items as $item) {
        $stmt_detalle->execute([
            $pedido_id,
            intval($item['id']),
            intval($item['cantidad']),
            floatval($item['precio'])
        ]);
    }

    // 5. Cambiar estado de la mesa a ocupada
    $sql_mesa = "UPDATE mesas SET estado = 'ocupada' WHERE id = ?";
    $stmt_mesa = $pdo->prepare($sql_mesa);
    $stmt_mesa->execute([$real_mesa_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Pedido procesado correctamente.']);

} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>