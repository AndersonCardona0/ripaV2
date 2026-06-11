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

$esAdmin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador');

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
            .bg-secondary { background-color: #DAA520; }
            .border-secondary { border-color: #DAA520; }
            .bg-dots {
                background-image: radial-gradient(#e5e7eb 1.5px, transparent 1.5px);
                background-size: 12px 12px;
            }
        </style>
    </head>
    <body class="flex h-screen overflow-hidden text-gray-800">
        
        <?php include __DIR__ . '/../Utilities/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col">
            <?php include __DIR__ . '/../utilities/header.php'; ?>

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
                    <div class="flex items-center gap-2">
                        <button id="btn-toggle-grupo" onclick="toggleGrupoMode()"
                                class="p-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-gray-400 hover:text-[#BC5F40] hover:border-orange-200 transition-all flex items-center gap-2"
                                title="Vista agrupada">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <span class="btn-grupo-label text-xs font-semibold">Agrupar</span>
                        </button>
                        <?php if ($esAdmin): ?>
                        <button onclick="abrirConfiguracionSalon()"
                                class="p-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-gray-400 hover:text-[#BC5F40] hover:border-orange-200 transition-all flex items-center justify-center group"
                                title="Configuración del Salón">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                    

                <div id="grid-mesas">
                </div>
            </main>
        </div>

        <!-- Modal de Configuración del Salón -->
         <div id="modal-config-salon" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm animate-inside">
            <div class="bg-[#FBF9F6] rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden border border-gray-100">
                <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-100">
                    <div class="flex items-center space-x-2 text-gray-800">
                        <h2 class="text-base font-bold text-gray-900">Configuración del Salón</h2>
                    </div>
                    <button onclick="document.getElementById('modal-config-salon').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total de Mesas</label>
                        <div class="flex items-center bg-gray-100/80 border border-gray-200/30 rounded-2xl p-2 px-4 justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-400 text-lg">🪑</span>
                                <input type="number" id="input-total-mesas" value="0" min="1" max="50" readonly
                                       class="bg-transparent font-bold text-gray-800 focus:outline-none w-12 text-center text-lg">
                            </div>
                            <div class="flex space-x-1.5">
                                <button type="button" onclick="cambiarCantidadMesas(-1)" class="w-8 h-8 rounded-xl bg-white shadow-sm flex items-center justify-center font-bold text-gray-600 hover:bg-gray-50 active:scale-95 transition">-</button>
                                <button type="button" onclick="cambiarCantidadMesas(1)" class="w-8 h-8 rounded-xl bg-white shadow-sm flex items-center justify-center font-bold text-gray-600 hover:bg-gray-50 active:scale-95 transition">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200/60 bg-white rounded-2xl p-4 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Previsualización de Estructura</span>
                            <div class="flex space-x-1">
                                <span class="w-2 h-2 rounded-full bg-amber-700"></span>
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span class="w-2 h-2 rounded-full bg-amber-200"></span>
                            </div>
                        </div>
                        
                        <div class="bg-dots w-full min-h-[140px] max-h-[200px] overflow-y-auto rounded-xl p-4 border border-dashed border-gray-200/80 flex flex-wrap gap-2.5 justify-center items-center" id="previsualizacion-grid">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-config-salon').classList.add('hidden')"
                            class="px-4 py-2.5 rounded-xl bg-transparent font-semibold text-gray-500 hover:bg-gray-100 transition text-xs">
                        Cancelar
                    </button>
                    <button type="button" onclick="guardarConfiguracionMesas()"
                            class="px-5 py-2.5 rounded-xl bg-[#BC5F40] hover:bg-[#a65236] text-white font-semibold shadow-md shadow-orange-700/10 transition text-xs flex items-center space-x-2">
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </div>
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
        <!-- Modal PIN Mesero -->
        <div id="modal-pin" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-7 mx-4">

                <!-- Título -->
                <h2 class="text-xl font-bold text-center text-[#BC5F40] mb-7">Abrir Nueva Mesa</h2>

                <!-- Visualizador del PIN -->
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 text-center">Código de Mesero</label>
                    <div class="relative bg-stone-50 border border-stone-200 rounded-xl h-14 flex items-center justify-center">
                        <span id="pin-display" class="text-2xl tracking-[0.6em] text-gray-800 font-mono select-none min-h-[1em] text-center"></span>
                        <button onclick="pinKey('del')" class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100 active:scale-95 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="pin-error" class="text-xs text-red-500 mt-2 transition-opacity duration-200" style="opacity:0">PIN incorrecto. Intenta de nuevo.</p>
                </div>

                <!-- Teclado numérico: 1–9 y 0 centrado -->
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <button onclick="pinKey('1')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">1</button>
                    <button onclick="pinKey('2')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">2</button>
                    <button onclick="pinKey('3')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">3</button>
                    <button onclick="pinKey('4')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">4</button>
                    <button onclick="pinKey('5')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">5</button>
                    <button onclick="pinKey('6')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">6</button>
                    <button onclick="pinKey('7')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">7</button>
                    <button onclick="pinKey('8')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">8</button>
                    <button onclick="pinKey('9')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">9</button>
                    <div></div>
                    <button onclick="pinKey('0')" class="h-14 rounded-xl bg-white border border-stone-200 text-gray-900 font-semibold text-lg hover:bg-stone-50 active:scale-95 active:bg-stone-100 transition-all">0</button>
                    <div></div>
                </div>

                <!-- Confirmar -->
                <button id="pin-confirm-btn" onclick="pinConfirmar()"
                        class="w-full bg-[#BC5F40] hover:bg-[#a04e35] active:scale-[0.98] text-white font-bold py-4 rounded-2xl transition-all shadow-md shadow-[#BC5F40]/20 mb-3 disabled:opacity-60">
                    Confirmar Apertura
                </button>

                <!-- Cancelar -->
                <div class="text-center">
                    <button onclick="cerrarModalPin()" class="text-sm font-medium text-stone-400 hover:text-stone-700 py-2 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <script src="/js/mesas.js"></script>
        <?php include __DIR__ . '/../utilities/footer.php'; ?>
    </body>
</html>