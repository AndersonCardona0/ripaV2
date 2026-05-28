<?php
$password_original = "123456"; // Pon aquí la contraseña que quieras para el mesero
$hash = password_hash($password_original, PASSWORD_DEFAULT);

echo "La contraseña hasheada es: " . $hash;
?>