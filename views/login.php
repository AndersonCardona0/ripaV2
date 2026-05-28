<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");  
    exit();
}


$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];


    // Consulta preparada para evitar inyección SQL
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();


    if ($user && password_verify($password, $user['password'])) {
        // Login exitoso
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['nombre'] = $user['nombre'];

        header("Location: index.php");
        exit();
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Artisanal POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #FBF9F6; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-sm border border-gray-100">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-[#BC5F40] rounded-xl flex items-center justify-center mx-auto mb-4 text-white text-2xl">
                🍴
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Artisanal POS</h1>
            <p class="text-gray-500 text-sm">Gestión artesanal para tu panadería</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text" name="usuario" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#BC5F40] outline-none">
            </div>
            
            <div class="mb-6">
                <div class="flex justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <a href="#" class="text-xs text-[#BC5F40] font-semibold hover:underline">¿Olvidaste tu contraseña?</a>
                </div>
                <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#BC5F40] outline-none">
            </div>

            <button type="submit" class="w-full bg-[#BC5F40] hover:bg-[#9e4e34] text-white font-bold py-3 rounded-xl transition shadow-lg shadow-orange-100">
                Iniciar Sesión →
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            ¿No tienes una cuenta? <a href="#" class="text-[#BC5F40] font-semibold">Solicitar acceso</a>
        </div>
    </div>

    <footer class="fixed bottom-8 text-gray-400 text-xs tracking-widest uppercase">
        Tradición & Tecnología
    </footer>
</body>
</html>