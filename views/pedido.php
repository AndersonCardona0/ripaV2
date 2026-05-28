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
                <button onclick="cambiarCategoria(1, this)" class="category-btn w-full bg-amber-100 text-amber-800 font-medium py-3 px-4 rounded-xl flex items-center mb-2 dynamic-active">
                    <span class="mr-3">🍞</span> Panadería
                </button>
                <button onclick="cambiarCategoria(2, this)" class="category-btn w-full text-gray-500 hover:bg-gray-50 font-medium py-3 px-4 rounded-xl flex items-center mb-2">
                    <span class="mr-3">🍰</span> Pastelería
                </button>
                <button onclick="cambiarCategoria(3, this)" class="category-btn w-full text-gray-500 hover:bg-gray-50 font-medium py-3 px-4 rounded-xl flex items-center mb-2">
                    <span class="mr-3">☕</span> Cafetería
                </button>
                <button onclick="cambiarCategoria(4, this)" class="category-btn w-full text-gray-500 hover:bg-gray-50 font-medium py-3 px-4 rounded-xl flex items-center">
                    <span class="mr-3">🥤</span> Bebidas Frías
                </button>
            </div>
            
            <button onclick="window.location.href='index.php'" class="w-full border border-gray-300 text-gray-600 font-semibold py-3 px-4 rounded-xl hover:bg-gray-50 transition">
                ⬅️ Volver a Mesas
            </button>
        </div>
        <div class="text-xs text-gray-400">Mesero Activo: Carlos M.</div>
    </div>

    <div class="flex-1 flex flex-col">
        <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0">
            <h1 class="text-xl font-bold text-gray-900">Mesa <?php echo str_pad($mesa_activa, 2, '0', STR_PAD_LEFT); ?> <span class="text-sm font-normal text-gray-400 ml-2">Tomando Pedido...</span></h1>
            <input type="text" placeholder="Search products..." class="bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none w-64">
        </header>

        <main class="flex-1 p-8 overflow-y-auto">
            <div id="grid-productos" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 overflow-y-auto overflow-x-hidden w-full max-w-full p-1">
        </main>
    </div>

    <div class="w-full md:w-96 bg-white border-l border-stone-200 p-6 flex flex-col h-screen sticky top-0">
    
        <div class="flex justify-between items-center mb-6 flex-shrink-0">
            <h2 class="text-xl font-bold text-gray-800">Orden</h2>
            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-lg">
                T-<?php echo str_pad(isset($_GET['mesa']) ? intval($_GET['mesa']) : 0, 2, "0", STR_PAD_LEFT); ?>
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
            <button type="button" 
                    onclick="openPaymentModal()" 
                    class="w-full mb-3 bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-2xl flex items-center justify-center transition shadow-lg shadow-green-700/20">
                <span class="ml-2">Pagar Mesa</span>
            </button>
            <?php endif; ?>
            <button onclick="enviarACocina()" class="w-full bg-amber-700 hover:bg-amber-800 text-white font-bold py-4 px-6 rounded-2xl flex items-center justify-center space-x-2 transition shadow-lg shadow-amber-700/20">
                <span>Guardar Pedido</span>
            </button>
        </div>
</div>

    <script>
        const mesaActiva = <?php echo $mesa_activa; ?>;
        let carrito = [];

        // 1. Cargar productos desde la API de forma dinámica
        function cargarProductos(categoriaId) {
            fetch(`../controllers/api_productos.php?categoria=${categoriaId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const grid = document.getElementById('grid-productos');
                        grid.innerHTML = '';
                        
                        res.data.forEach(prod => {
                            grid.innerHTML += `
                                <div onclick="agregarAlCarrito(${prod.id}, '${prod.nombre}', ${prod.precio})" 
                                    class="w-full bg-white border border-stone-200 rounded-2xl p-4 flex flex-col justify-between cursor-pointer hover:shadow-md transition h-40 box-border">
                                    
                                    <div class="w-full flex flex-col gap-y-1">
                                        <h3 class="font-bold text-gray-800 text-sm sm:text-base leading-snug line-clamp-2" title="${prod.nombre}">
                                            ${prod.nombre}
                                        </h3>
                                        
                                        <span class="font-extrabold text-amber-700 text-sm sm:text-base mt-1">
                                            $${parseFloat(prod.precio).toFixed(2)}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-gray-400 border-t border-stone-100 pt-2 w-full mt-auto">
                                        <span>Ver detalle</span>
                                        <span class="text-amber-500 font-bold bg-stone-50 px-2 py-1 rounded-lg border border-stone-100">🛒+</span>
                                    </div>
                                </div>`;
                        });
                    }
                });
        }

        // 2. Manejo de pestañas/categorías
        function cambiarCategoria(id, boton) {
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-amber-100', 'text-amber-800', 'font-medium');
                btn.classList.add('text-gray-500');
            });
            boton.classList.add('bg-amber-100', 'text-amber-800', 'font-medium');
            cargarProductos(id);
        }

        // 3. Agregar productos al carrito temporal
        function agregarAlCarrito(id, nombre, precio) {
            const existe = carrito.find(item => item.id === id);
            if (existe) {
                existe.cantidad++;
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1 });
            }
            actualizarInterfazCarrito();
        }

        // 4. Pintar el carrito en el panel derecho con botones de control
        function actualizarInterfazCarrito() {
            const contenedor = document.getElementById('carrito-items');
            const vacio = document.getElementById('carrito-vacio');
            
            if (carrito.length === 0) {
                vacio.style.display = 'block';
                document.querySelectorAll('#txt-subtotal, #txt-impuesto, #txt-total').forEach(el => el.innerText = '$0.00');
                // Limpiamos cualquier fila residual
                document.querySelectorAll('.cart-item-row').forEach(el => el.remove());
                return;
            }
            
            vacio.style.display = 'none';
            document.querySelectorAll('.cart-item-row').forEach(el => el.remove());

            let subtotal = 0;

            carrito.forEach(item => {
                const itemSubtotal = item.precio * item.cantidad;
                subtotal += itemSubtotal;

                const row = document.createElement('div');
                row.className = 'cart-item-row flex justify-between items-center bg-stone-50 p-3 rounded-xl text-sm border border-stone-100 mb-2';
                row.innerHTML = `
                    <div class="flex-1 pr-1">
                        <span class="font-bold text-gray-800 block line-clamp-1">${item.nombre}</span>
                        <span class="text-xs text-gray-400">$${parseFloat(item.precio).toFixed(2)} x ${item.cantidad}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white border border-gray-200 rounded-lg p-1 mr-2">
                        <button onclick="modificarCantidad(${item.id}, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-stone-100 rounded-md font-bold text-sm transition">-</button>
                        <span class="font-semibold text-gray-800 px-1 min-w-[12px] text-center">${item.cantidad}</span>
                        <button onclick="modificarCantidad(${item.id}, 1)" class="w-6 h-6 flex items-center justify-center text-primary hover:bg-stone-100 rounded-md font-bold text-sm transition">+</button>
                    </div>
                    
                    <div class="flex items-center space-x-3 min-w-[90px] justify-end">
                        <span class="font-bold text-gray-700">$${itemSubtotal.toFixed(2)}</span>
                        <button onclick="eliminarProducto(${item.id})" class="text-red-400 hover:text-red-600 transition font-medium text-xs p-1" title="Eliminar del pedido">
                            ❌
                        </button>
                    </div>
                `;
                contenedor.appendChild(row);
            });

            // Recalculamos los bloques de totales globales
            const impuesto = subtotal * 0.08;
            const total = subtotal + impuesto;

            document.getElementById('txt-subtotal').innerText = `$${subtotal.toFixed(2)}`;
            document.getElementById('txt-impuesto').innerText = `$${impuesto.toFixed(2)}`;
            document.getElementById('txt-total').innerText = `$${total.toFixed(2)}`;
        }


            function modificarCantidad(id, cambio) {
                // Forzamos que ambos IDs sean tratados como números enteros
                const item = carrito.find(p => parseInt(p.id) === parseInt(id));
                
                if (item) {
                    item.cantidad += cambio;
                    
                    // Si llega a 0 o menos, lo removemos
                    if (item.cantidad <= 0) {
                        eliminarProducto(id);
                        return; // Cortamos la ejecución aquí
                    }
                    
                    actualizarInterfazCarrito();
                }
            }

            function eliminarProducto(id) {
                carrito = carrito.filter(p => parseInt(p.id) !== parseInt(id));
                actualizarInterfazCarrito();
            }

        function enviarACocina() {
            if (carrito.length === 0) {
                alert('Por favor selecciona al menos un producto antes de enviar.');
                return;
            }

            const datosPedido = {
                mesa_id: mesaActiva,
                items: carrito
            };

            fetch('../controllers/guardar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datosPedido)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    alert(res.message); 
                    window.location.href = 'index.php'; 
                } else {
                    alert('Error al guardar: ' + res.message);
                }
            })
            .catch(err => {
                console.error('Error en la petición:', err);
                alert('Ocurrió un error de red al intentar enviar el pedido.');
            });
        }

        cargarProductos(1);

        function verificarPedidoExistente() {
            fetch(`../controllers/api_obtener_pedido_mesa.php?mesa=${mesaActiva}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data.length > 0) {
                        // Si la mesa ya tenía cosas, las metemos al carrito de JavaScript
                        carrito = res.data.map(item => ({
                            id: parseInt(item.id),
                            nombre: item.nombre,
                            precio: parseFloat(item.precio),
                            cantidad: parseInt(item.cantidad)
                        }));
                        actualizarInterfazCarrito();
                    }
                })
                .catch(err => console.error("Error cargando pedido previo:", err));
        }


        cargarProductos(1);
        verificarPedidoExistente();
    </script>
    </script>
        <div id="modal-pago" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 max-h-[90vh] flex flex-col">
                <div class="p-8 border-b md:border-b-0 md:border-r border-gray-100">
                    <h3 class="text-xl font-bold mb-6">Resumen de la Cuenta</h3>
                    <div id="modal-resumen-items" class="space-y-4 mb-8 overflow-y-auto flex-1 max-h-[40vh] pr-2">
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
    <script src="../js/pedidos.js"></script>
    <?php include __DIR__ . '/../Utilities/footer.php'; ?>
</body>
</html>