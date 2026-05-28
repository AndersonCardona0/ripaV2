<?php
// 1. Verificamos si NO existe una sesión activa antes de intentar iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpiamos y destruimos
session_unset(); 
session_destroy(); 

// 3. Redirigimos usando ruta absoluta (asegúrate de incluir la / al principio)
header("Location: /views/login.php"); 
exit();
?>