<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/conexion.php'; 

try {
    $sql = "SELECT 
                m.id AS id,
                m.numero_mesa AS numero_mesa, 
                m.estado AS estado,
                IFNULL(SUM(p.total), 0) AS total_balance
            FROM 
                mesas m
            LEFT JOIN 
                pedidos p ON m.id = p.mesa_id AND p.estado = 'pendiente' 
            GROUP BY 
                m.id, m.numero_mesa, m.estado";
            
    $stmt = $pdo->query($sql);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success', 
        'data' => $mesas ? $mesas : [],
        'user_role' => $_SESSION['rol'] ?? 'invitado'
    ], JSON_UNESCAPED_UNICODE);

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'data' => []
    ]);
}
?>