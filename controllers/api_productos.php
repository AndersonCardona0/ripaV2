<?php
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? ''; // Capturamos el ID (estará lleno solo en modo edición)
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 1;
    
    // Lógica para el procesamiento y subida de imágenes
    $nombre_imagen = null; 
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName = $_FILES['imagen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $extensionesPermitidas)) {
            $nombre_imagen = 'prod_' . uniqid() . '_' . time() . '.' . $fileExtension;
            $directorioSubida = '../uploads/productos/';
            
            if (!is_dir($directorioSubida)) {
                mkdir($directorioSubida, 0755, true);
            }
            
            $rutaDestino = $directorioSubida . $nombre_imagen;
            if (!move_uploaded_file($fileTmpPath, $rutaDestino)) {
                $nombre_imagen = null;
            }
        }
    }
    
    try {
        if (!empty($id)) {

            if ($nombre_imagen !== null) {
                // Si subió una imagen nueva, se actualizan todos los campos incluyendo la imagen nueva
                $sql = "UPDATE productos SET nombre = ?, precio = ?, categoria_id = ?, imagen = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $precio, $categoria_id, $nombre_imagen, $id]);
            } else {
                // Si NO subió imagen, se actualizan los datos pero se respeta la imagen que ya existía
                $sql = "UPDATE productos SET nombre = ?, precio = ?, categoria_id = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $precio, $categoria_id, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Producto actualizado con éxito']);
        } else {

            $sql = "INSERT INTO productos (nombre, precio, categoria_id, imagen) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $precio, $categoria_id, $nombre_imagen]);
            echo json_encode(['status' => 'success', 'message' => 'Producto guardado con éxito']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit(); 
}

try {
    // Blindamos el isset para verificar que no sea una cadena vacía ni el texto 'undefined' de JS
    if (isset($_GET['categoria']) && $_GET['categoria'] !== '' && $_GET['categoria'] !== 'undefined') {
        
        $categoria_id = intval($_GET['categoria']);
        
        $sql = "SELECT id, nombre, precio, imagen, categoria_id FROM productos WHERE categoria_id = :categoria_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['categoria_id' => $categoria_id]);
        
    } else {
        // Si no se envía categoría (carga inicial), traemos todos los productos del catálogo por defecto
        $sql = "SELECT id, nombre, precio, imagen, categoria_id FROM productos";
        $stmt = $pdo->query($sql);
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $productos]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>