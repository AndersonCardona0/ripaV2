<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

// Seguridad: Solo admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

// Recibir los datos enviados desde JS
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit();
}

$mesa_id = intval($data['mesa_id']);
$subtotal = floatval($data['subtotal']);
$impuesto = floatval($data['impuesto']);
$total = floatval($data['total']);
$metodo = $data['metodo'] ?? 'tarjeta'; // Valor por defecto

try {
    $pdo->beginTransaction();

    // Guardar la venta en la tabla histórica
    $stmt = $pdo->prepare("INSERT INTO ventas (mesa_id, subtotal, impuesto, total, metodo_pago) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$mesa_id, $subtotal, $impuesto, $total, $metodo]);

    // Liberar la mesa 
    $stmt = $pdo->prepare("UPDATE mesas SET estado = 'libre' WHERE id = ?");
    $stmt->execute([$mesa_id]);

    // Limpiar los productos asociados a esta mesa
    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE mesa_id = ? AND estado = 'pendiente'");
    $stmt->execute([$mesa_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Pago procesado y mesa liberada exitosamente']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>