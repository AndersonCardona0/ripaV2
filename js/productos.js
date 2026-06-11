window.productosCargados = [];
window.categoriaActivaId = null;

document.addEventListener('DOMContentLoaded', async () => {
    await cargarCategorias();
    cargarProductos(1);

    const inputConfirmar = document.getElementById('input-confirmar-nombre');
    if (inputConfirmar) {
        inputConfirmar.addEventListener('input', () => {
            const nombreEsperado = document.getElementById('nombre-categoria-a-eliminar').innerText.trim();
            const btn = document.getElementById('btn-confirmar-eliminar');
            const coincide = inputConfirmar.value === nombreEsperado;
            btn.disabled = !coincide;
            btn.classList.toggle('opacity-40', !coincide);
            btn.classList.toggle('cursor-not-allowed', !coincide);
        });
    }
});

async function cargarCategorias() {
    const contenedor = document.getElementById('contenedor-categorias');
    const select = document.getElementById('select-categorias');

    if (!contenedor && !select) {
        return; 
    }

    try {
        const res = await fetch('../controllers/api_categorias.php');
        const json = await res.json();
        
        if (contenedor) {
            contenedor.innerHTML = json.data.map(cat => `
                <button onclick="cargarProductos(${cat.id})" class="px-4 py-2 bg-white rounded-full border shadow-sm hover:border-[#BC5F40]">
                    ${cat.nombre}
                </button>
            `).join('');
        }

        if (select) {
            select.innerHTML = json.data.map(cat => `
                <option value="${cat.id}">${cat.nombre}</option>
            `).join('');
        }

        const selectEliminar = document.getElementById('select-eliminar-categoria');
        if (selectEliminar) {
            selectEliminar.innerHTML = json.data.map(cat =>
                `<option value="${cat.id}">${cat.nombre}</option>`
            ).join('');
        }

    } catch (error) {
        console.error("Error al cargar categorías:", error);
    }
}


async function cargarProductos(catId) {
    window.categoriaActivaId = catId;
    try {
        const res = await fetch(`../controllers/api_productos.php?categoria=${catId}`);
        if (!res.ok) throw new Error("Error al conectar con la API");
        const data = await res.json();
        
        window.productosCargados = data.data || [];

        const tbody = document.getElementById('tabla-productos');

        if (tbody) {
            if (window.productosCargados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-400 text-sm">No hay productos en esta categoría.</td></tr>`;
                return;
            }

            tbody.innerHTML = window.productosCargados.map(p => {
                const htmlImagen = p.imagen 
                    ? `<img src="../uploads/productos/${p.imagen}" class="w-12 h-12 object-cover rounded-xl border border-gray-100 shadow-sm" alt="${p.nombre}">`
                    : `<div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 text-[10px] font-bold border border-gray-200/50 select-none">Sin foto</div>`;

                return `
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-800">${p.nombre || 'Sin nombre'}</td>
                        <td class="px-6 py-4 text-gray-600">$${parseFloat(p.precio || 0).toLocaleString()}</td>
                        <td class="px-6 py-4">${htmlImagen}</td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-3 items-center">
                                <button onclick="abrirEditarProducto(${p.id})" 
                                        class="text-blue-500 hover:text-blue-700 font-semibold transition-colors">
                                    Editar
                                </button>
                                <span class="text-gray-200">|</span>
                                <button onclick="eliminarProducto(${p.id}, ${p.categoria_id})"
                                        class="text-red-500 hover:text-red-700 font-semibold transition-colors">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
    } catch (error) {
        console.error("Error al cargar productos:", error);
    }
}

async function guardarProducto(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('../controllers/api_productos.php', { 
        method: 'POST', 
        body: formData 
    });
    
    const data = await res.json();
    
    if (data.status === 'success') {
        cerrarModalProducto();
        // Refrescamos la lista usando el id de categoría del formulario
        cargarProductos(formData.get('categoria_id')); 
    } else {
        alert("Error del servidor: " + data.message);
    }
}

async function guardarCategoria(event) {
    event.preventDefault();
    const form = event.target;
    const nombre = form.querySelector('[name="nombre"]').value.trim();
    if (!nombre) return;

    abrirModalConfirmacion({
        iconBg: 'bg-orange-50',
        iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#BC5F40]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>`,
        titulo: '¿Crear categoría?',
        descripcion: 'Se añadirá una nueva categoría visible en el catálogo de productos.',
        previewVisual: `<div class="w-full h-full bg-orange-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#BC5F40]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </div>`,
        nombre,
        detalle: 'Nueva categoría',
        labelConfirmar: 'Crear categoría',
        onConfirmar: async () => {
            try {
                const res = await fetch('../controllers/api_categorias.php', { method: 'POST', body: new FormData(form) });
                const data = await res.json();
                if (data.status === 'success') {
                    document.getElementById('modal-categoria').classList.add('hidden');
                    form.reset();
                    await cargarCategorias();
                } else {
                    alert('Error al guardar la categoría: ' + (data.message || 'Error desconocido'));
                }
            } catch (err) {
                console.error('Error:', err);
            }
        }
    });
}

function abrirAgregarProducto() {
    document.getElementById('form-producto').reset();
    document.getElementById('producto-id').value = '';
    document.getElementById('modal-producto-titulo').innerText = 'Agregar Producto';
    document.getElementById('aviso-imagen').classList.add('hidden');
    document.getElementById('modal-producto').classList.remove('hidden');
}

function abrirEditarProducto(id) {
    const producto = window.productosCargados.find(p => p.id == id);

    if (!producto) {
        console.error("No se encontró el producto localmente con ID:", id);
        return;
    }

    document.getElementById('modal-producto-titulo').innerText = 'Editar Producto';
    document.getElementById('producto-id').value = producto.id;
    document.getElementById('producto-nombre').value = producto.nombre || '';
    document.getElementById('producto-precio').value = producto.precio || 0;
    document.getElementById('select-categorias').value = producto.categoria_id || '';
    document.getElementById('aviso-imagen').classList.remove('hidden');
    document.getElementById('modal-producto').classList.remove('hidden');
}

function abrirModalEliminarCategoria() {
    document.getElementById('fase-1-seleccion').classList.remove('hidden');
    document.getElementById('fase-2-confirmacion').classList.add('hidden');
    document.getElementById('input-confirmar-nombre').value = '';
    document.getElementById('error-eliminar-categoria').classList.add('hidden');
    document.getElementById('btn-confirmar-eliminar').disabled = true;
    document.getElementById('btn-confirmar-eliminar').classList.add('opacity-40', 'cursor-not-allowed');
    document.getElementById('modal-eliminar-categoria').classList.remove('hidden');
}

function cerrarModalEliminarCategoria() {
    document.getElementById('modal-eliminar-categoria').classList.add('hidden');
}

function continuarEliminacion() {
    const select = document.getElementById('select-eliminar-categoria');
    const selectedOption = select.options[select.selectedIndex];
    const nombre = selectedOption ? selectedOption.text.trim() : '';
    if (!nombre) return;

    document.getElementById('nombre-categoria-a-eliminar').innerText = nombre;
    document.getElementById('input-confirmar-nombre').value = '';
    document.getElementById('error-eliminar-categoria').classList.add('hidden');
    document.getElementById('btn-confirmar-eliminar').disabled = true;
    document.getElementById('btn-confirmar-eliminar').classList.add('opacity-40', 'cursor-not-allowed');

    document.getElementById('fase-1-seleccion').classList.add('hidden');
    document.getElementById('fase-2-confirmacion').classList.remove('hidden');
}

function volverFase1() {
    document.getElementById('fase-2-confirmacion').classList.add('hidden');
    document.getElementById('fase-1-seleccion').classList.remove('hidden');
}

async function confirmarEliminarCategoria() {
    const select = document.getElementById('select-eliminar-categoria');
    const categoriaId = select.value;
    const errorEl = document.getElementById('error-eliminar-categoria');
    errorEl.classList.add('hidden');

    try {
        const res = await fetch(`../controllers/api_categorias.php?id=${categoriaId}`, { method: 'DELETE' });
        const data = await res.json();

        if (data.status === 'success') {
            cerrarModalEliminarCategoria();
            window.categoriaActivaId = null;
            await cargarCategorias();
            const primerBoton = document.querySelector('#contenedor-categorias button');
            if (primerBoton) primerBoton.click();
        } else {
            errorEl.innerText = data.message;
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error al eliminar categoría:', error);
        errorEl.innerText = 'Ocurrió un error de red. Intenta de nuevo.';
        errorEl.classList.remove('hidden');
    }
}

async function eliminarProducto(id, categoriaId) {
    const producto = window.productosCargados.find(p => p.id == id);
    if (!producto) return;

    const previewVisual = producto.imagen
        ? `<img src="../uploads/productos/${producto.imagen}" class="w-full h-full object-cover" alt="${producto.nombre}">`
        : `<div class="w-full h-full bg-gray-100 flex items-center justify-center">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
               </svg>
           </div>`;

    abrirModalConfirmacion({
        iconBg: 'bg-rose-50',
        iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>`,
        titulo: '¿Eliminar producto?',
        descripcion: 'Esta acción no se puede deshacer. El producto y su imagen serán eliminados permanentemente del catálogo.',
        previewVisual,
        nombre: producto.nombre,
        detalle: `$${parseFloat(producto.precio).toLocaleString()}`,
        labelConfirmar: 'Sí, eliminar',
        onConfirmar: async () => {
            try {
                const res = await fetch(`../controllers/api_productos.php?id=${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (data.status === 'success') {
                    cargarProductos(categoriaId);
                } else {
                    alert('Error al eliminar: ' + data.message);
                }
            } catch (error) {
                console.error('Error al eliminar producto:', error);
            }
        }
    });
}

function cerrarModalProducto() {
    document.getElementById('modal-producto').classList.add('hidden');
    document.getElementById('form-producto').reset();
    document.getElementById('producto-id').value = '';
}
