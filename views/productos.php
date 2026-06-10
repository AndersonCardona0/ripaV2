<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
verificarAutenticacion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Artisanal POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');
        body { font-family: 'Montserrat', sans-serif; background-color: #FBF9F6; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <?php include __DIR__ . '/../utilities/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 z-10">
            <button id="toggle-sidebar" class="mr-4 p-3 text-gray-500 hover:bg-gray-100 rounded-lg flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav class="flex items-center space-x-8 font-medium text-gray-500 h-full">
                <a href="dashboard.php" class="hover:text-[#BC5F40] transition h-full flex items-center border-b-2 border-transparent hover:border-[#BC5F40]">
                    Dashboard
                </a>
                
                <a href="mesas.php" class="hover:text-[#BC5F40] transition h-full flex items-center border-b-2 border-transparent hover:border-[#BC5F40]">
                    Mesas
                </a>
            </nav>
            
            <div class="w-12"></div>
        </header>

        <div class="flex-1 px-10 overflow-y-auto">
            
            <div id="contenedor-categorias" class="flex gap-4 mb-8 overflow-x-auto pb-2">
                </div>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Gestión de Productos</h2>
                <div class="flex items-center gap-2">
                    <button onclick="abrirModalEliminarCategoria()"
                            class="flex items-center gap-2 border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 px-4 py-3 rounded-xl font-semibold transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar Categoría
                    </button>
                    <button onclick="document.getElementById('modal-categoria').classList.remove('hidden')"
                            class="border-2 border-[#BC5F40] text-[#BC5F40] hover:bg-stone-50 px-6 py-3 rounded-xl font-semibold shadow-sm transition">
                        + Nueva Categoría
                    </button>
                </div>
                <button onclick="abrirAgregarProducto()"
                        class="bg-[#BC5F40] text-white px-6 py-3 rounded-xl hover:bg-[#a04e35] shadow-lg transition">
                    + Nuevo Producto
                </button>
            </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <table class="w-full text-left border-collapse">
                <thead class="text-gray-400 uppercase text-xs font-bold border-b border-gray-50">
                    <tr>
                        <th class="px-6 py-4">Producto</th>
                        <th class="px-6 py-4">Precio</th>
                        <th class="px-6 py-4">Imagen</th>
                        <th class="px-6 py-4">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-productos" class="divide-y divide-gray-50">
                </tbody>
            </table>
        </div>
    </div>
</main>

<div id="modal-producto" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">
        
        <h2 id="modal-producto-titulo" class="text-2xl font-bold text-gray-800 mb-6">Agregar Producto</h2>
        
        <form id="form-producto" onsubmit="guardarProducto(event)" enctype="multipart/form-data" class="space-y-4">
            
            <input type="hidden" name="id" id="producto-id">

            <input type="text" name="nombre" id="producto-nombre" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none" placeholder="Nombre del producto" required>
            
            <input type="number" step="0.01" name="precio" id="producto-precio" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none" placeholder="Precio" required>
            
            <select name="categoria_id" id="select-categorias" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none">
            </select>

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider pl-1">Imagen del Producto</label>
                <input type="file" name="imagen" accept="image/*" class="w-full p-3 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#BC5F40]/10 file:text-[#BC5F40] hover:file:bg-[#BC5F40]/20 cursor-pointer">
                
                <p id="aviso-imagen" class="text-[11px] text-gray-400 pl-1 hidden">💡 Selecciona un archivo solo si deseas cambiar la imagen actual.</p>
            </div>

            <div class="flex gap-4 mt-8">
                <button type="button" onclick="cerrarModalProducto()" 
                        class="flex-1 p-4 bg-gray-100 rounded-2xl text-gray-600 font-semibold">Cancelar</button>
                <button type="submit" 
                        class="flex-1 p-4 bg-[#BC5F40] text-white rounded-2xl font-semibold hover:bg-[#a04e35]">Guardar</button>
            </div>
        </form>
    </div>
</div>

    <div id="modal-categoria" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Agregar Categoría</h2>
            <form id="form-categoria" onsubmit="guardarCategoria(event)" class="space-y-4">
                <input type="text" name="nombre" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none" placeholder="Nombre de la categoría" required>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="document.getElementById('modal-categoria').classList.add('hidden')" 
                            class="flex-1 p-4 bg-gray-100 rounded-2xl text-gray-600 font-semibold">Cancelar</button>
                    <button type="submit" 
                            class="flex-1 p-4 bg-[#BC5F40] text-white rounded-2xl font-semibold hover:bg-[#a04e35]">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Eliminar Categoría (Doble Confirmación) -->
    <div id="modal-eliminar-categoria" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl w-full max-w-md shadow-2xl">

            <!-- Fase 1: Selección de categoría -->
            <div id="fase-1-seleccion">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Eliminar Categoría</h2>
                <p class="text-sm text-gray-400 mb-6">Selecciona la categoría que deseas eliminar.</p>

                <select id="select-eliminar-categoria"
                        class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none text-gray-700">
                </select>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="cerrarModalEliminarCategoria()"
                            class="flex-1 p-4 bg-gray-100 rounded-2xl text-gray-600 font-semibold hover:bg-gray-200 transition">Cancelar</button>
                    <button type="button" onclick="continuarEliminacion()"
                            class="flex-1 p-4 bg-[#BC5F40] text-white rounded-2xl font-semibold hover:bg-[#a04e35] transition">Continuar</button>
                </div>
            </div>

            <!-- Fase 2: Confirmación por nombre -->
            <div id="fase-2-confirmacion" class="hidden">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">¿Confirmar eliminación?</h2>
                </div>

                <p class="text-sm text-gray-500 mb-1">Vas a eliminar permanentemente:</p>
                <p id="nombre-categoria-a-eliminar" class="font-bold text-gray-800 text-lg mb-5 bg-gray-50 px-4 py-2 rounded-xl"></p>

                <p class="text-sm text-gray-400 mb-2">Para confirmar, escribe el nombre exacto de la categoría:</p>
                <input type="text" id="input-confirmar-nombre" autocomplete="off"
                       class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-red-400 outline-none text-gray-700"
                       placeholder="Escribe el nombre aquí...">

                <p id="error-eliminar-categoria" class="hidden text-sm text-red-500 bg-red-50 px-4 py-3 rounded-xl mt-4"></p>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="volverFase1()"
                            class="flex-1 p-4 bg-gray-100 rounded-2xl text-gray-600 font-semibold hover:bg-gray-200 transition">Volver</button>
                    <button type="button" id="btn-confirmar-eliminar" onclick="confirmarEliminarCategoria()"
                            disabled
                            class="flex-1 p-4 bg-red-500 text-white rounded-2xl font-semibold opacity-40 cursor-not-allowed transition">
                        Confirmar Eliminación
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script src="../js/productos.js?v=<?php echo time(); ?>"></script>
    <?php include __DIR__ . '/../utilities/footer.php'; ?>
</body>
</html>