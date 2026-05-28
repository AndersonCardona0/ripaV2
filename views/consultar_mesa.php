<?php
require_once 'conexion.php';

$numero_mesa_a_consultar = 2;

try {
    // 1. Consultar el estado actual de la mesa y los datos generales del pedido pendiente
    $sql_pedido = "SELECT m.numero_mesa, m.estado AS mesa_estado, p.id AS pedido_id, p.fecha, u.nombre AS mesero 
                   FROM mesas m
                   INNER JOIN pedidos p ON m.id = p.mesa_id
                   INNER JOIN usuarios u ON p.usuario_id = u.id
                   WHERE m.numero_mesa = :numero_mesa AND p.estado = 'pendiente'";
    
    $stmt = $pdo->prepare($sql_pedido);
    $stmt->execute(['numero_mesa' => $numero_mesa_a_consultar]);
    $info_mesa = $stmt->fetch();

    // Si la mesa existe y tiene un pedido pendiente
    if ($info_mesa) {
        echo "<h2>Estatus de la Mesa N° " . $info_mesa['numero_mesa'] . "</h2>";
        echo "<p><strong>Estado de la mesa:</strong> " . strtoupper($info_mesa['mesa_estado']) . "</p>";
        echo "<p><strong>Atendido por:</strong> " . $info_mesa['mesero'] . "</p>";
        echo "<p><strong>Hora de apertura:</strong> " . $info_mesa['fecha'] . "</p>";
        echo "<hr>";
        echo "<h3>Ítems montados en la mesa:</h3>";

        // 2. Consultar los productos específicos cargados a ese pedido
        $sql_detalles = "SELECT prod.nombre AS producto, dp.cantidad, dp.precio_unitario, (dp.cantidad * dp.precio_unitario) AS subtotal
                         FROM detalle_pedidos dp
                         INNER JOIN productos prod ON dp.producto_id = prod.id
                         WHERE dp.pedido_id = :pedido_id";
        
        $stmt_detalles = $pdo->prepare($sql_detalles);
        $stmt_detalles->execute(['pedido_id' => $info_mesa['pedido_id']]);
        $items = $stmt_detalles->fetchAll();

        // Dibujamos una tabla sencilla para visualizar los ítems
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<thead>
                <tr style='background-color: #f2f2f2;'>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
              </thead>";
        echo "<tbody>";
        
        $total_acumulado = 0;
        foreach ($items as $item) {
            echo "<tr>";
            echo "<td>" . $item['producto'] . "</td>";
            echo "<td>" . $item['cantidad'] . "</td>";
            echo "<td>$" . number_format($item['precio_unitario'], 2) . "</td>";
            echo "<td>$" . number_format($item['subtotal'], 2) . "</td>";
            echo "</tr>";
            $total_acumulado += $item['subtotal'];
        }
        
        echo "</tbody>";
        echo "</table>";
        
        echo "<h3>Total de la cuenta: $" . number_format($total_acumulado, 2) . "</h3>";

    } else {
        // En caso de que la mesa esté libre o no tenga pedidos activos
        echo "<p>La Mesa N° $numero_mesa_a_consultar actualmente se encuentra libre y sin consumos registrados.</p>";
    }

} catch (\PDOException $e) {
    echo "Error al realizar la consulta: " . $e->getMessage();
}
?>