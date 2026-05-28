<?php 
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/conexion.php';
verificarAutenticacion(); 

// Lógica del Modal de Avisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_aviso'])) {
    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);
    
    if (!empty($titulo) && !empty($mensaje)) {
        $stmt = $pdo->prepare("INSERT INTO avisos (titulo, mensaje, activo) VALUES (?, ?, 1)");
        $stmt->execute([$titulo, $mensaje]);
        header("Location: /index.php"); 
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head> 
        
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Artisanal POS - Mapa de Mesas</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/css/style.css">

        <style>
            body { font-family: 'Montserrat', sans-serif; background-color: #FBF9F6; }
            .bg-primary { background-color: #BC5F40; }
            .text-primary { color: #BC5F40; }
            /* CORREGIDO: Se cambió 'S' por '5' para que el color hexadecimal sea válido (#DAA520) */
            .bg-secondary { background-color: #DAA520; }
            .border-secondary { border-color: #DAA520; }
        </style>
    </head>
    <body class="flex h-screen overflow-hidden text-gray-800">
        
        <?php include __DIR__ . '/../Utilities/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col">
            <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 z-10">
                <button id="toggle-sidebar" class="mr-4 p-3 text-gray-500 hover:bg-gray-100 rounded-lg flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <nav class="flex items-center space-x-6 font-medium text-gray-500 h-full">
                </nav>

                <nav class="flex items-center space-x-6 font-medium text-gray-500 h-full">
                    <a href="dashboard.php" class="hover:text-primary transition py-2">Dashboard</a>
                    
                    <a href="index.php" class="text-primary border-b-2 border-primary h-full flex items-center transition">
                        Mesas
                    </a>
                    
                </nav>
                
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Buscar Tabla..." class="bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none w-64">
                </div>
            </header>

            <main class="flex-1 p-8 overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Comedor Principal</h1>
                        <div class="flex space-x-4 text-xs font-medium text-gray-500 mt-1">
                            <span class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> 
                                <span id="count-available" class="count-margin">0</span> Disponibles
                            </span>
                            <span class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span> 
                                <span id="count-occupied" class="count-margin">8</span> Ocupadas
                            </span>
                        </div>
                    </div>
                </div>

                <div id="grid-mesas" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                </div>
            </main>
        </div>

        <!-- Modal de Avisos -->
        <div id="modal-avisos" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Aviso Operativo</h2>
                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Título</label>
                            <input type="text" name="titulo" required placeholder="Ej: Sin postres hoy" 
                                class="w-full mt-1 p-3 bg-gray-50 border border-gray-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#BC5F40]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Especificaciones</label>
                            <textarea name="mensaje" required placeholder="Detalles..." 
                                    class="w-full mt-1 p-3 bg-gray-50 border border-gray-100 rounded-xl h-24 focus:outline-none focus:ring-2 focus:ring-[#BC5F40]"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('modal-avisos').classList.add('hidden')"
                                class="flex-1 p-3 rounded-xl bg-gray-100 text-gray-600 font-semibold hover:bg-gray-200">Cancelar</button>
                        <button type="submit" name="crear_aviso" 
                                class="flex-1 p-3 rounded-xl bg-[#BC5F40] text-white font-semibold hover:bg-amber-800">Publicar</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="modal-confirm" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white p-8 rounded-3xl w-full max-w-sm shadow-2xl animate-in fade-in zoom-in duration-200">
                <h3 id="confirm-title" class="text-lg font-bold text-gray-800">¿Estás seguro?</h3>
                <p id="confirm-msg" class="text-sm text-gray-500 mt-2 mb-6">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <button onclick="closeConfirmModal()" class="flex-1 p-3 rounded-xl bg-gray-100 text-gray-600 font-semibold hover:bg-gray-200">Cancelar</button>
                    <a id="confirm-action-btn" href="#" class="flex-1 p-3 rounded-xl bg-red-500 text-white font-semibold text-center hover:bg-red-600">Confirmar</a>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../Utilities/footer.php'; ?>
    </body>
</html>