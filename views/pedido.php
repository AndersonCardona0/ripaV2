<?php
require_once __DIR__ . '/../config/auth.php';
verificarAutenticacion();

$mesa_activa = isset($_GET['mesa']) ? intval($_GET['mesa']) : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisanal POS - Pedido Mesa <?php echo $mesa_activa; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #FBF9F6; }
        .bg-primary { background-color: #BC5F40; }
        .text-primary { color: #BC5F40; }
        .border-primary { border-color: #BC5F40; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-800" data-mesa-id="<?php echo htmlspecialchars($mesa_activa); ?>">

    <div class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between p-6">
        <div>
            <div class="text-xl font-bold text-primary mb-8 tracking-wide">Artisanal POS</div>
            <div class="mb-6">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-3">Menú Rápido</span>
                <div id="contenedor-botones-categorias" class="space-y-2"></div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col">
        <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0">
            <h1 class="text-xl font-bold text-gray-900">Mesa <?php echo str_pad($mesa_activa, 2, '0', STR_PAD_LEFT); ?> <span class="text-sm font-normal text-gray-400 ml-2">Tomando Pedido...</span></h1>
            <button onclick="window.location.href='index.php'" class="border border-gray-300 text-gray-600 font-semibold py-2 px-4 rounded-xl hover:bg-gray-50 transition text-sm">
                ⬅ Volver a Mesas
            </button>
        </header>

        <main class="flex-1 p-6 overflow-y-auto">
            <div id="grid-productos" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 overflow-y-auto overflow-x-hidden w-full max-w-full p-1"></div>
        </main>
    </div>

    <div class="w-full md:w-96 bg-white border-l border-stone-200 p-6 flex flex-col h-screen sticky top-0">
        <div class="flex justify-between items-center mb-6 flex-shrink-0">
            <h2 class="text-xl font-bold text-gray-800">Orden</h2>
            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-lg">
                Mesa-<?php echo str_pad($mesa_activa, 2, "0", STR_PAD_LEFT); ?>
            </span>
        </div>

        <div id="carrito-items" class="flex-1 overflow-y-auto pr-1 space-y-2 mb-6 style-scrollbar">
            <div id="carrito-vacio" class="text-center py-12 text-gray-400">
                <span class="text-4xl block mb-2">🛒</span>
                <p class="text-sm">Orden Vacía</p>
            </div>
        </div>

        <div class="border-t border-stone-200 pt-4 flex-shrink-0 bg-white">
            <div class="flex justify-between text-sm text-gray-500 mb-2">
                <span>Subtotal</span>
                <span id="txt-subtotal" class="font-semibold text-gray-700">$0.00</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500 mb-4">
                <span>Tax (8%)</span>
                <span id="txt-impuesto" class="font-semibold text-gray-700">$0.00</span>
            </div>
            <hr class="border-dashed border-stone-200 mb-4">
            <div class="flex justify-between items-center mb-6">
                <span class="text-base font-bold text-gray-800">Total</span>
                <span id="txt-total" class="text-2xl font-black text-gray-900">$0.00</span>
            </div>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
            <button type="button" onclick="abrirConfirmacionPago()" class="w-full mb-3 bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-2xl flex items-center justify-center transition shadow-lg shadow-green-700/20">
                <span class="ml-2">Pagar Mesa</span>
            </button>
            <?php endif; ?>
            <button onclick="enviarACocina()" class="w-full bg-amber-700 hover:bg-amber-800 text-white font-bold py-4 px-6 rounded-2xl flex items-center justify-center space-x-2 transition shadow-lg shadow-amber-700/20">
                <span>Guardar Pedido</span>
            </button>
        </div>
    </div>

    <div id="modal-pago" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 max-h-[90vh]">
            <div class="p-8 border-b md:border-b-0 md:border-r border-gray-100 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold mb-6">Resumen de la Cuenta</h3>
                    <div id="modal-resumen-items" class="space-y-4 mb-8 overflow-y-auto max-h-[40vh] pr-2"></div>
                </div>
                <div class="border-t border-dashed pt-4 space-y-2">
                    <div class="flex justify-between text-gray-500"><span>Subtotal</span><span id="m-subtotal">$0.00</span></div>
                    <div class="flex justify-between text-gray-500"><span>Impuesto (8%)</span><span id="m-impuesto">$0.00</span></div>
                    <div class="flex justify-between font-bold text-lg text-gray-800"><span>Total</span><span id="m-total">$0.00</span></div>
                </div>
            </div>

            <div class="p-8 bg-gray-50">
                <h3 class="text-xl font-bold mb-6">Método de Pago</h3>
                <div class="flex gap-2 mb-6">
                    <button class="flex-1 py-3 bg-white border border-gray-200 rounded-xl font-medium">Efectivo</button>
                    <button class="flex-1 py-3 bg-white border-2 border-[#BC5F40] text-[#BC5F40] rounded-xl font-bold">Tarjeta</button>
                    <button class="flex-1 py-3 bg-white border border-gray-200 rounded-xl font-medium">Digital</button>
                </div>

                <input type="text" placeholder="Ej. Juan Pérez" class="w-full p-3 rounded-xl border border-gray-200 mb-4">
                <input type="text" placeholder="0000 0000 0000 0000" class="w-full p-3 rounded-xl border border-gray-200 mb-4">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <input type="text" placeholder="MM/AA" class="p-3 rounded-xl border border-gray-200">
                    <input type="text" placeholder="***" class="p-3 rounded-xl border border-gray-200">
                </div>

                <button onclick="procesarPagoFinal()" class="w-full bg-[#BC5F40] text-white font-bold py-4 rounded-xl hover:bg-amber-800 transition shadow-lg">
                    Finalizar Pago <span id="m-total-btn">$0.00</span>
                </button>
                <p class="text-[10px] text-gray-400 mt-4 text-center">Pago procesado de forma segura bajo estándares PCI-DSS.</p>
            </div>
        </div>
    </div>

    <script src="../js/pedido.js"></script>
    <script src="../js/pedidos.js"></script>
    <?php include __DIR__ . '/../Utilities/footer.php'; ?>
</body>
</html>
