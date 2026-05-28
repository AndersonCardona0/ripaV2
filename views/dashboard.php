<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';

verificarAutenticacion(); 

//Logica de eliminación de avisos (Admin)
if (isset($_GET['borrar']) && $_SESSION['rol'] === 'administrador') {
    $stmt = $pdo->prepare("DELETE FROM avisos WHERE id = ?");
    $stmt->execute([$_GET['borrar']]);
    header("Location: dashboard.php");
    exit();
}
// Lógica del Modal de Avisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_aviso'])) {
    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);
    
    if (!empty($titulo) && !empty($mensaje)) {
        $stmt = $pdo->prepare("INSERT INTO avisos (titulo, mensaje, activo) VALUES (?, ?, 1)");
        $stmt->execute([$titulo, $mensaje]);
        header("Location: dashboard.php"); 
        exit();
    }
}



try {
    //Consulta de Avisos activos
    $stmt_avisos = $pdo->query("SELECT id, titulo, mensaje FROM avisos WHERE activo = 1 ORDER BY fecha_creacion DESC");
    $avisos_activos = $stmt_avisos->fetchAll(PDO::FETCH_ASSOC);

    // Mesas Activas
    $stmt_mesas_act = $pdo->query("SELECT COUNT(*) as activas FROM mesas WHERE estado != 'Available'");
    $mesas_activas = $stmt_mesas_act->fetch()['activas'];

} catch(Exception $e) {
    $avisos_activos = [];
    $mesas_activas = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisanal POS - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #FBF9F6; }
        .bg-primary { background-color: #BC5F40; }
        .text-primary { color: #BC5F40; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800">
    
    <?php include __DIR__ . '/../utilities/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-y-auto">
        <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0">
            <button id="toggle-sidebar" class="mr-4 p-2 text-gray-500 hover:bg-gray-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav class="flex items-center space-x-6 font-medium text-gray-500 h-full">
            </nav>
            <nav class="flex items-center space-x-6 font-medium text-gray-500 h-full">
                <a href="dashboard.php" class="text-primary border-b-2 border-primary h-full flex items-center transition">Dashboard</a>
                
                <a href="index.php" class="hover:text-primary transition py-2">
                    Mesas
                </a>
            </nav>
            <div class="flex items-center space-x-4">
                <input type="text" placeholder="Buscar Tabla..." class="bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none w-64">
            </div>
        </header>

        <main class="p-8 space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Resúmen Diario</h1>
                <p class="text-sm text-gray-400">Bienvenido, ten un buen día.</p>
            </div>    
                <!--Tablero de Avisos -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h3 class="font-bold mb-3 flex items-center text-stone-800">Avisos del Día</h3>
                <div class="space-y-3 text-sm">
                    <?php if (count($avisos_activos) > 0): ?>
                        <?php foreach($avisos_activos as $aviso): ?>
                            <div class="border-b pb-3 flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-gray-800">
                                        <?php echo htmlspecialchars($aviso['titulo'] ?? 'Sin título'); ?>
                                    </p>
                                    <p class="text-gray-600 mt-0.5">
                                        <?php echo htmlspecialchars($aviso['mensaje'] ?? ''); ?>
                                    </p>
                                </div>

                                <?php if ($_SESSION['rol'] === 'administrador'): ?>
                                    <button 
                                        type="button"
                                        onclick="openConfirmModal(
                                            'Eliminar Aviso', 
                                            '¿Estás seguro de que deseas eliminar este aviso? Esta acción no se puede deshacer.', 
                                            '?borrar=<?php echo $aviso['id']; ?>', 
                                            true
                                        )"
                                        class="text-red-500 hover:text-red-700 font-bold ml-4"
                                    >
                                        ×
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-400 italic">No hay avisos hoy.</p>
                    <?php endif; ?>
                </div>
            </div> 

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <span class="text-xs text-gray-400 font-semibold block uppercase">Mesas Activas</span>
                <span class="text-3xl font-bold text-gray-900 block mt-2"><?php echo $mesas_activas; ?>/20</span>
                <div class="w-full bg-gray-100 h-1.5 rounded-full mt-4 overflow-hidden">
                    <div class="bg-amber-600 h-full" style="width: <?php echo ($mesas_activas/20)*100; ?>%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">            
                <div class="space-y-6">

                    <div class="bg-primary text-white p-6 rounded-3xl shadow-lg relative overflow-hidden">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-75">Top Server</h4>
                        <div class="flex items-center space-x-4 mt-4">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100" class="w-12 h-12 rounded-full border-2 border-white/50 object-cover">
                            <div>
                                <h3 class="text-lg font-bold">Elena R.</h3>
                                <p class="text-xs opacity-90">14 Orders Delivered</p>
                            </div>
                        </div>
                    </div>
                </div>
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
        <?php include __DIR__ . '/../utilities/footer.php'; ?>
    </body>
</html>