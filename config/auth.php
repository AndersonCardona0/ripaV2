<?php
require_once __DIR__ . '/../config/auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de rol
function verificarEsAdmin() {
    verificarAutenticacion();
    if ($_SESSION['rol'] !== 'admin') {
        header("Location: index.php?error=acceso_denegado");
        exit();
    }
}


// Función para forzar que el navegador no guarde caché
function deshabilitarCache() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
}

function verificarAutenticacion() {
    deshabilitarCache();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /login.php"); // O la ruta a tu login
        exit();
    }
}
?>