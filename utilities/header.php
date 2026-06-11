<?php
/*
 * utilities/header.php — Componente de encabezado global de Artisanal POS.
 *
 * Estrategia de layout (3 columnas simétricas con CSS Grid):
 *   grid-cols-3 divide el header en tres zonas de igual ancho (33.3 % c/u).
 *   La columna central siempre ocupa exactamente el mismo segmento del viewport,
 *   sin importar cuánto contenido tengan las columnas izquierda o derecha.
 *   Esto ancla la navegación al centro geométrico de la pantalla en todas las vistas,
 *   eliminando los saltos de maquetación al navegar entre páginas.
 */

// sidebar.php ya define $pagina_actual; este fallback cubre inclusiones directas.
$pagina_actual = $pagina_actual ?? basename($_SERVER['PHP_SELF']);

// Closure que devuelve las clases Tailwind correctas según el estado del enlace.
$navCls = fn(string $pagina): string => $pagina === $pagina_actual
    ? 'border-b-2 border-[#BC5F40] text-[#BC5F40] font-semibold'
    : 'border-b-2 border-transparent text-gray-500 hover:text-[#BC5F40] hover:border-[#BC5F40] transition-colors';
?>

<!--
    header — flex-shrink-0 impide que el bloque se comprima en contenedores flex.
    La altura h-20 es fija en todas las vistas para evitar cualquier Layout Shift.
-->
<header class="h-20 bg-white border-b border-gray-100 flex-shrink-0 z-10">

    <!--
        grid-cols-3: tres columnas de igual ancho (33.3 % cada una).
        ┌──────────────┬──────────────────────┬──────────────┐
        │  Toggle + logo  │   NAV (siempre aquí)  │  Perfil      │
        └──────────────┴──────────────────────┴──────────────┘
        Al ser columnas de grid (no flex), ninguna "empuja" a la otra:
        la columna central es inmutable aunque las laterales varíen.
    -->
    <div class="grid grid-cols-3 h-full w-full items-center px-6">

        <!-- ─── Columna izquierda: toggle del sidebar + marca ──────────────── -->
        <div class="flex items-center gap-3 min-w-0">
            <button id="toggle-sidebar"
                    class="p-2.5 text-gray-400 hover:bg-gray-100 rounded-xl transition-colors flex-shrink-0"
                    aria-label="Alternar menú lateral">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <!-- Visible solo en sm+ para no saturar pantallas pequeñas -->
            <span class="text-sm font-bold text-[#BC5F40] tracking-wide truncate hidden sm:block">
                Artisanal POS
            </span>
        </div>

        <!-- ─── Columna central: navegación principal ──────────────────────
             justify-center posiciona el nav en el centro exacto del tercio
             central. Como ese tercio siempre mide 33.3 % del viewport, la
             posición horizontal de los botones es invariable entre vistas.   -->
        <nav class="flex justify-center items-center h-full gap-1 text-sm font-medium">

            <a href="/views/dashboard.php"
               class="px-4 h-full flex items-center <?= $navCls('dashboard.php') ?>">
                Dashboard
            </a>

            <a href="/views/index.php"
               class="px-4 h-full flex items-center <?= $navCls('index.php') ?>">
                Mesas
            </a>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
            <a href="/views/productos.php"
               class="px-4 h-full flex items-center <?= $navCls('productos.php') ?>">
                Productos
            </a>
            <?php endif; ?>

        </nav>

        <!-- ─── Columna derecha: perfil del usuario ────────────────────────── -->
        <div class="flex justify-end items-center gap-3 min-w-0">
            <!-- Nombre y rol: ocultos en móvil para no desbordar -->
            <div class="text-right hidden md:block min-w-0">
                <p class="text-xs font-bold text-gray-800 truncate leading-tight">
                    <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                </p>
                <p class="text-xs text-gray-400 capitalize leading-tight">
                    <?= htmlspecialchars($_SESSION['rol'] ?? '') ?>
                </p>
            </div>
            <!-- Avatar con inicial o icono genérico -->
            <div class="w-9 h-9 rounded-full bg-[#BC5F40]/10 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#BC5F40]"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>

    </div>
</header>
