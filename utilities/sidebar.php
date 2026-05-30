<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    .no-transition { transition: none !important; }
</style>

<div id="sidebar" class="w-0 w-64 transition-all duration-300 ease-in-out
 overflow-hidden bg-white border-r border-gray-100 flex flex-col h-screen flex-shrink-0 whitespace-nowrap">
    
    <div class="p-6 border-b border-gray-50">
        <div class="text-xl font-bold text-[#BC5F40] mb-6 tracking-wide" id="brand-name">Artisanal POS</div>
        
        <div class="flex items-center space-x-3 mb-4">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=50" class="w-10 h-10 rounded-full" alt="User">
            <div id="user-info">
                <p class="text-sm font-bold text-gray-800"><?php echo $_SESSION['nombre']; ?></p>
                <p class="text-xs text-gray-500 capitalize"><?php echo $_SESSION['rol']; ?></p>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <?php if ($_SESSION['rol'] === 'administrador'): ?>
            <a href="/views/productos.php" class="block p-3 rounded-xl transition-all duration-200 <?php echo ($pagina_actual == 'productos.php') ? 'bg-orange-50 text-[#BC5F40] font-bold' : 'text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40]'; ?>">
                Productos
            </a>
            <a href="/views/inventario.php" class="block p-3 rounded-xl transition-all duration-200 <?php echo ($pagina_actual == 'inventario.php') ? 'bg-orange-50 text-[#BC5F40] font-bold' : 'text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40]'; ?>">
                Inventario
            </a>
            <button onclick="document.getElementById('modal-avisos').classList.remove('hidden')" 
                    class="w-full text-left p-3 rounded-xl transition-all duration-200 text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40] active:scale-95">
                Crear Aviso
            </button>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-gray-100 space-y-2">
        <a href="#" class="block p-3 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition">Configuración</a>
        <a href="#" class="block p-3 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition">Soporte</a>
        <a href="/logout.php" class="flex items-center p-3 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-700 font-semibold mt-2 transition">Cerrar Sesión</a>
    </div>
</div>

<div id="modal-avisos" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Aviso Operativo</h2>
            <form id="form-crear-aviso" method="POST">
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

<script>
    (function() {
        const sidebar = document.getElementById('sidebar');
        
        if (localStorage.getItem('sidebarState') === 'closed') {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
        }

        setTimeout(() => {
            sidebar.classList.add('transition-all', 'duration-300', 'ease-in-out');
        }, 50);
    })();
</script>