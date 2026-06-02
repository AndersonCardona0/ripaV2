<?php
// 1. Asegurar que la respuesta siempre sea JSON
header('Content-Type: application/json');

try {
    // 2. Incluir tu archivo de conexión real
    require_once __DIR__ . '/../config/conexion.php'; 

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('La variable de conexión $pdo no está disponible o no es válida.');
    }
    
    $conexion = $pdo; 

    // 3. Leer el flujo de entrada JSON desde el fetch
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);

    if (!isset($data['total_mesas'])) {
        throw new Exception('No se especificó la cantidad de mesas.');
    }

    $nuevoTotal = intval($data['total_mesas']);

    if ($nuevoTotal < 1 || $nuevoTotal > 50) {
        throw new Exception('La cantidad de mesas debe estar entre 1 y 50.');
    }
    
    // 4. Consultar la cantidad y estado actual de las mesas en la BD
    $stmt = $conexion->query("SELECT numero_mesa, estado FROM mesas ORDER BY numero_mesa ASC");
    $mesasActuales = $stmt->fetchAll();
    $cantidadActual = count($mesasActuales);

    // Iniciamos la transacción segura
    $conexion->beginTransaction(); 

    if ($nuevoTotal > $cantidadActual) {
        // ==========================================
        // CASO A: AGREGAR MESAS NUEVAS (CORREGIDO)
        // ==========================================
        // Eliminamos 'total_balance' de la consulta ya que no existe en tu tabla
        $stmtInsert = $conexion->prepare("INSERT INTO mesas (numero_mesa, estado) VALUES (:num, 'libre')");
        
        for ($i = $cantidadActual + 1; $i <= $nuevoTotal; $i++) {
            $stmtInsert->execute([':num' => $i]);
        }

    } elseif ($nuevoTotal < $cantidadActual) {
        // ==========================================
        // CASO B: REDUCIR EL SALÓN
        // ==========================================
        $mesasAEliminar = array_filter($mesasActuales, function($mesa) use ($nuevoTotal) {
            return intval($mesa['numero_mesa']) > $nuevoTotal;
        });

        foreach ($mesasAEliminar as $mesa) {
            if ($mesa['estado'] !== 'libre') {
                throw new Exception("No se puede reducir el salón. La Mesa T-" . str_pad($mesa['numero_mesa'], 2, '0', STR_PAD_LEFT) . " está ocupada o tiene una cuenta activa.");
            }
        }

        // Si todas están limpias, procedemos con el DELETE
        $stmtDelete = $conexion->prepare("DELETE FROM mesas WHERE numero_mesa > :nuevoTotal");
        $stmtDelete->execute([':nuevoTotal' => $nuevoTotal]);
    }

    // Confirmamos los cambios de forma definitiva
    $conexion->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Configuración del salón actualizada correctamente.',
        'detalles' => [
            'antes' => $cantidadActual,
            'ahora' => $nuevoTotal
        ]
    ]);

} catch (Throwable $e) {
    // Revertimos cambios solo si la transacción alcanzó a inicializarse
    if (isset($conexion) && $conexion instanceof PDO && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el backend: ' . $e->getMessage()
    ]);
}