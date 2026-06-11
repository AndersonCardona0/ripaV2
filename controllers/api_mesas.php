<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $accion = $data['accion'] ?? '';

    if ($accion === 'abrir_mesa') {
        $mesa_id = intval($data['mesa_id'] ?? 0);
        $pin     = trim($data['pin'] ?? '');

        if (!$mesa_id || $pin === '') {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, nombre FROM meseros WHERE pin = ?");
        $stmt->execute([$pin]);
        $mesero = $stmt->fetch();

        if (!$mesero) {
            echo json_encode(['status' => 'pin_incorrecto', 'message' => 'PIN incorrecto']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, estado FROM mesas WHERE id = ?");
        $stmt->execute([$mesa_id]);
        $mesa = $stmt->fetch();

        if (!$mesa) {
            echo json_encode(['status' => 'error', 'message' => 'Mesa no encontrada']);
            exit;
        }

        // Solo abre la mesa si estaba libre; si ya está ocupada solo valida el PIN
        if ($mesa['estado'] === 'libre') {
            $stmt = $pdo->prepare(
                "UPDATE mesas SET estado = 'ocupada', mesero_id = ?, mesero_nombre = ?, fecha_apertura = NOW() WHERE id = ?"
            );
            $stmt->execute([$mesero['id'], $mesero['nombre'], $mesa_id]);
        }

        echo json_encode(['status' => 'success', 'mesero_nombre' => $mesero['nombre']]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);
    exit;
}

// GET: listar todas las mesas
try {
    $sql = "SELECT
                m.id,
                m.numero_mesa,
                m.estado,
                m.mesero_nombre,
                m.fecha_apertura,
                CASE
                    WHEN m.fecha_apertura IS NOT NULL
                    THEN CONCAT(
                        FLOOR(TIMESTAMPDIFF(MINUTE, m.fecha_apertura, NOW()) / 60), 'h ',
                        LPAD(MOD(TIMESTAMPDIFF(MINUTE, m.fecha_apertura, NOW()), 60), 2, '0'), 'm'
                    )
                    ELSE NULL
                END AS tiempo_ocupada,
                IFNULL(SUM(p.total), 0) AS total_balance
            FROM mesas m
            LEFT JOIN pedidos p ON m.id = p.mesa_id AND p.estado = 'pendiente'
            GROUP BY m.id, m.numero_mesa, m.estado, m.mesero_nombre, m.fecha_apertura";

    $stmt  = $pdo->query($sql);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status'    => 'success',
        'data'      => $mesas ?: [],
        'user_role' => $_SESSION['rol'] ?? 'invitado'
    ], JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'data' => []]);
}
?>