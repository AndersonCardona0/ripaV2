<?php
    session_start();
    header('Content-Type: application/json');
    require_once '../config/conexion.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

        if (!empty($titulo) && !empty($mensaje)) {
            try {
                // Ajusta los nombres de tu tabla y columnas
                $stmt = $pdo->prepare("INSERT INTO avisos (titulo, mensaje, fecha_creacion) VALUES (?, ?, NOW())");
                $stmt->execute([$titulo, $mensaje]);

                $instance = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 1);
                if ($instance) {
                    fwrite($instance, "refrescar_avisos\n");
                    fclose($instance);
                }
                
                echo json_encode(['status' => 'success', 'message' => 'Aviso publicado con éxito']);
            } catch (Exception $e) {
                // echo json_encode(['status' => 'error', 'message' => 'Error en base de datos']);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios']);
        }
    }
?>