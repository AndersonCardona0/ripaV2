<?php
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 1;

    $nombre_imagen = null; 
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName = $_FILES['imagen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validamos formatos permitidos
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $extensionesPermitidas)) {
            // Generamos un nombre único para evitar duplicados en el servidor
            $nombre_imagen = 'prod_' . uniqid() . '_' . time() . '.' . $fileExtension;
            $directorioSubida = '../uploads/productos/';
            
            // Si la carpeta no existe, la crea automáticamente con permisos
            if (!is_dir($directorioSubida)) {
                mkdir($directorioSubida, 0755, true);
            }
            
            $rutaDestino = $directorioSubida . $nombre_imagen;
            if (!move_uploaded_file($fileTmpPath, $rutaDestino)) {
                $nombre_imagen = null; // Si falla la subida física, guardamos null
            }
        }
    }
    
    try {
        $sql = "INSERT INTO productos (nombre, precio, categoria_id, imagen) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $precio, $categoria_id, $nombre_imagen]);
        
        echo json_encode(['status' => 'success', 'message' => 'Producto guardado']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit(); 
}

try {

    if (isset($_GET['categoria'])) {
        $categoria_id = intval($_GET['categoria']);
        $sql = "SELECT id, nombre, precio, imagen FROM productos WHERE categoria_id = :categoria_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['categoria_id' => $categoria_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM productos");
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $productos]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>