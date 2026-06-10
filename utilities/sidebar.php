<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<style>
    /*
     * Bloquea TODAS las transiciones durante el render inicial.
     * Se aplica vía html.no-transition para no contaminar el resto del CSS.
     */
    html.no-transition *,
    html.no-transition *::before,
    html.no-transition *::after {
        transition: none !important;
    }

    /*
     * Estado colapsado: se activa con html.sb-collapsed antes del primer paint.
     * Usa !important para sobrescribir la clase Tailwind w-64 que queda en el DOM
     * como valor de referencia (el CDN la necesita para generar la utilidad).
     */
    html.sb-collapsed #sidebar {
        width: 0 !important;
    }
</style>

<!--
    SCRIPT BLOQUEANTE — corre sincrónicamente durante el parseo del HTML,
    ANTES de que el navegador calcule estilos o pinte cualquier píxel.
    Lee localStorage e inyecta las clases de estado en <html> de inmediato.
-->
<script>
    (function () {
        var html = document.documentElement;
        /* Congela transiciones para que el posicionamiento inicial sea instantáneo */
        html.classList.add('no-transition');
        /* Aplica estado colapsado si el usuario lo dejó así */
        if (localStorage.getItem('sidebarState') === 'closed') {
            html.classList.add('sb-collapsed');
        }
    }());
</script>

<div id="sidebar"
     class="w-64 transition-all duration-300 ease-in-out overflow-hidden bg-white border-r border-gray-100 flex flex-col h-screen flex-shrink-0 whitespace-nowrap">

    <div class="p-6 border-b border-gray-50">
        <div class="text-xl font-bold text-[#BC5F40] mb-6 tracking-wide" id="brand-name">Artisanal POS</div>

        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-[#BC5F40]/10 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#BC5F40]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div id="user-info">
                <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <?php if ($_SESSION['rol'] === 'administrador'): ?>
            <a href="/views/dashboard.php"
               class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?php echo ($pagina_actual === 'dashboard.php') ? 'bg-orange-50 text-[#BC5F40] font-bold' : 'text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40]'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="/views/productos.php"
               class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?php echo ($pagina_actual === 'productos.php') ? 'bg-orange-50 text-[#BC5F40] font-bold' : 'text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40]'; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Productos</span>
            </a>
            <button onclick="document.getElementById('modal-avisos').classList.remove('hidden')"
                    class="w-full flex items-center gap-3 p-3 rounded-xl transition-all duration-200 text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40] active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>Crear Aviso</span>
            </button>
        <?php endif; ?>

        <a href="/views/index.php"
           class="flex items-center gap-3 p-3 rounded-xl transition-all duration-200 <?php echo ($pagina_actual === 'index.php') ? 'bg-orange-50 text-[#BC5F40] font-bold' : 'text-gray-600 hover:bg-orange-50 hover:text-[#BC5F40]'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18M14 3v18M6 3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6a3 3 0 013-3z"/>
            </svg>
            <span>Mesas</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-100 space-y-1">
        <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Configuración</span>
        </a>
        <a href="/logout.php"
           class="flex items-center gap-3 p-3 rounded-xl text-red-500 hover:bg-red-50 hover:text-red-700 font-semibold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>

<!-- Modal de Avisos -->
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

<!--
    Habilita las transiciones luego de dos frames de animación:
    - Frame 1 (rAF externo): el navegador calculó estilos y pintó el estado inicial sin animación
    - Frame 2 (rAF interno): ya es seguro activar transition-all para interacciones del usuario
-->
<script>
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            document.documentElement.classList.remove('no-transition');
        });
    });
</script>
