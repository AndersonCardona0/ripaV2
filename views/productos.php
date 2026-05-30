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
            <button id="toggle-sidebar" class="p-3 text-gray-500 hover:bg-gray-100 rounded-lg flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <button onclick="document.getElementById('modal-producto').classList.remove('hidden')" 
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Agregar Producto</h2>
            <form id="form-producto" onsubmit="guardarProducto(event)" class="space-y-4">
                <input type="text" name="nombre" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none" placeholder="Nombre del producto" required>
                <input type="number" step="0.01" name="precio" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none" placeholder="Precio" required>
                
                <select name="categoria_id" id="select-categorias" class="w-full p-4 bg-gray-50 rounded-2xl border border-transparent focus:border-[#BC5F40] outline-none">
                </select>

                <div class="flex gap-4 mt-8">
                    <button type="button" onclick="document.getElementById('modal-producto').classList.add('hidden')" 
                            class="flex-1 p-4 bg-gray-100 rounded-2xl text-gray-600 font-semibold">Cancelar</button>
                    <button type="submit" 
                            class="flex-1 p-4 bg-[#BC5F40] text-white rounded-2xl font-semibold hover:bg-[#a04e35]">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/productos.js?v=<?php echo time(); ?>"></script>
    <?php include __DIR__ . '/../utilities/footer.php'; ?>
</body>
</html>