const mesaActiva = parseInt(document.body.getAttribute('data-mesa-id') || '1');
let carrito = [];
let categoriaSeleccionadaId = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarBotonesCategorias();
    verificarPedidoExistente();
});

function cargarBotonesCategorias() {
    fetch('../controllers/api_categorias.php')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
                const contenedor = document.getElementById('contenedor-botones-categorias');
                contenedor.innerHTML = '';

                res.data.forEach((cat, index) => {
                    const esActiva = index === 0;
                    if (esActiva && !categoriaSeleccionadaId) {
                        categoriaSeleccionadaId = cat.id;
                    }

                    const clasesBoton = esActiva
                        ? 'category-btn w-full bg-amber-100 text-amber-800 font-medium py-2.5 px-4 rounded-xl flex items-center text-left leading-tight transition-all dynamic-active'
                        : 'category-btn w-full text-gray-500 hover:bg-gray-50 hover:text-gray-800 font-medium py-2.5 px-4 rounded-xl flex items-center text-left leading-tight transition-all';

                    contenedor.innerHTML += `
                        <button onclick="cambiarCategoria(${cat.id}, this)" class="${clasesBoton}">
                            ${cat.nombre}
                        </button>
                    `;
                });

                if (categoriaSeleccionadaId) {
                    cargarProductos(categoriaSeleccionadaId);
                }
            }
        })
        .catch(err => console.error("Error al cargar categorías:", err));
}

function cargarProductos(categoriaId) {
    fetch(`../controllers/api_productos.php?categoria=${categoriaId}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const grid = document.getElementById('grid-productos');
                grid.innerHTML = '';

                res.data.forEach(prod => {
                    const imagenCard = prod.imagen
                        ? `<img src="../uploads/productos/${prod.imagen}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" alt="${prod.nombre}">`
                        : `<div class="w-full h-full flex flex-col items-center justify-center bg-stone-100 text-stone-300 gap-1 select-none">
                             <span class="text-xl">🍞</span>
                             <span class="text-[9px] font-bold uppercase tracking-wider">Sin Foto</span>
                           </div>`;

                    grid.innerHTML += `
                        <div onclick="agregarAlCarrito(${prod.id}, '${prod.nombre}', ${prod.precio})"
                             class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 flex flex-col justify-between cursor-pointer hover:shadow-md transition-all duration-200 w-full">
                            <div class="w-full aspect-square bg-stone-50 overflow-hidden relative border-b border-gray-50">
                                ${imagenCard}
                            </div>
                            <div class="p-3 flex flex-col gap-y-1">
                                <h3 class="font-bold text-gray-800 text-xs sm:text-sm tracking-tight line-clamp-1 group-hover:text-primary transition-colors" title="${prod.nombre}">
                                    ${prod.nombre}
                                </h3>
                                <div class="flex items-center justify-between mt-0.5">
                                    <span class="text-xs sm:text-sm font-extrabold text-[#BC5F40]">
                                        $${parseFloat(prod.precio).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}
                                    </span>
                                    <button class="w-7 h-7 bg-[#BC5F40] text-white rounded-lg flex items-center justify-center font-bold text-base shadow-sm hover:bg-[#a04e35] transition-transform active:scale-95">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>`;
                });
            }
        });
}

function cambiarCategoria(id, boton) {
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('bg-amber-100', 'text-amber-800', 'hover:text-gray-800');
        btn.classList.add('text-gray-500');
    });
    boton.classList.remove('text-gray-500');
    boton.classList.add('bg-amber-100', 'text-amber-800');
    cargarProductos(id);
}

function agregarAlCarrito(id, nombre, precio) {
    const existe = carrito.find(item => item.id === id);
    if (existe) {
        existe.cantidad++;
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1 });
    }
    actualizarInterfazCarrito();
}

function actualizarInterfazCarrito() {
    const contenedor = document.getElementById('carrito-items');
    const vacio = document.getElementById('carrito-vacio');

    if (carrito.length === 0) {
        vacio.style.display = 'block';
        document.querySelectorAll('#txt-subtotal, #txt-impuesto, #txt-total').forEach(el => el.innerText = '$0.00');
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
                <button onclick="eliminarProducto(${item.id})" class="text-red-400 hover:text-red-600 transition font-medium text-xs p-1">❌</button>
            </div>`;
        contenedor.appendChild(row);
    });

    const impuesto = subtotal * 0.08;
    const total = subtotal + impuesto;

    document.getElementById('txt-subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('txt-impuesto').innerText = `$${impuesto.toFixed(2)}`;
    document.getElementById('txt-total').innerText = `$${total.toFixed(2)}`;
}

function modificarCantidad(id, cambio) {
    const item = carrito.find(p => parseInt(p.id) === parseInt(id));
    if (item) {
        item.cantidad += cambio;
        if (item.cantidad <= 0) {
            eliminarProducto(id);
            return;
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

    fetch('../controllers/api_guardar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mesa_id: mesaActiva, items: carrito })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            window.location.href = 'index.php';
        } else {
            alert('Error al guardar: ' + res.message);
        }
    })
    .catch(err => {
        console.error('Error en la petición:', err);
        alert('Ocurrió un error de red.');
    });
}

function verificarPedidoExistente() {
    fetch(`../controllers/api_obtener_pedido_mesa.php?mesa=${mesaActiva}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
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
