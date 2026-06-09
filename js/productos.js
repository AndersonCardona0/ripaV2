
document.addEventListener('DOMContentLoaded', async () => {

    conectarWebSocket(); // Iniciamos la conexión WebSocket al cargar la página

    // 1. Lógica inicial de carga
    await cargarCategorias();
    cargarProductos(1);
    await refrescarListaAvisos();

});


async function refrescarListaAvisos() {

    console.log("🔄 Refrescando lista de avisos...");
    const contenedor = document.getElementById('contenedor-avisos');
    if (!contenedor) {
        console.warn("No se encontró el contenedor de avisos");
        return;
    }
    try {
        const response = await fetch('../controllers/api_leer_avisos.php?t=' + new Date().getTime());
        const result = await response.json();

        // Verificamos si el servidor contestó bien
        if (!response.ok) throw new Error("Error en la conexión con el servidor");
        

        
        // Si todo está bien, renderizamos
        if (result.data && result.data.length > 0) {
            contenedor.innerHTML = result.data.map(aviso => {
                const esAdmin = window.currentUserRole === 'administrador';
                const botonEliminar = esAdmin ? `
                <button type="button" 
                    onclick="openConfirmModal('Eliminar Aviso', '¿Estás seguro de que deseas eliminar este aviso? Esta acción no se puede deshacer.', '?borrar=${aviso.id}', true)" 
                    class="text-red-500 hover:text-red-700 font-bold ml-4 text-xl">
                ×
                </button>
                ` : '';
                return`
                    <div class="flex justify-between items-center border-b p-3 border-gray-100 bg-white">
                        <div>
                            <p class="font-bold text-gray-800">${aviso.titulo}</p>
                            <p class="text-gray-600 text-sm">${aviso.mensaje}</p>
                        </div>
                        <div>
                            ${botonEliminar}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            contenedor.innerHTML = '<p>No hay avisos por ahora.</p>';
        }
    } catch (error) {
        console.error("Fallo en el servicio de avisos:", error);
        contenedor.innerHTML = '<p class="text-red-500">Error cargando avisos. Intenta más tarde.</p>';
    }
}

async function cargarCategorias() {
    // 1. Buscamos los elementos
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
        
    } catch (error) {
        console.error("Error al cargar categorías:", error);
    }
}

// async function cargarProductos(catId) {
//     try {
//         // 1. PRIMERO: Hacemos la petición al servidor (independiente del DOM)
//         const res = await fetch(`../controllers/api_productos.php?categoria=${catId}`);
//         if (!res.ok) throw new Error("Error al conectar con la API");
//         const data = await res.json();
        
//         // 2. DESPUÉS: Buscamos el elemento y dibujamos solo si existe
//         const tbody = document.getElementById('tabla-productos');
        
//         if (tbody) {
//             tbody.innerHTML = data.data.map(p => `
//                 <tr>
//                     <td class="px-6 py-4 font-semibold">${p.nombre}</td>
//                     <td class="px-6 py-4">$${parseFloat(p.precio).toLocaleString()}</td>
//                     <td class="px-6 py-4"><button class="text-red-500">Eliminar</button></td>
//                 </tr>
//             `).join('');
//         } else {
            
//         }
//     } catch (error) {
//         console.error("Error al cargar productos:", error);
//     }
// }

async function cargarProductos(catId) {
    try {
        // 1. PRIMERO: Hacemos la petición al servidor
        const res = await fetch(`../controllers/api_productos.php?categoria=${catId}`);
        if (!res.ok) throw new Error("Error al conectar con la API");
        const data = await res.json();
        
        // 2. DESPUÉS: Buscamos el elemento y dibujamos solo si existe
        const tbody = document.getElementById('tabla-productos');
        
        if (tbody) {
            tbody.innerHTML = data.data.map(p => {
                // Generar el HTML de la miniatura dinámicamente
                const htmlImagen = p.imagen 
                    ? `<img src="../uploads/productos/${p.imagen}" class="w-12 h-12 object-cover rounded-xl border border-gray-100 shadow-sm" alt="${p.nombre}">`
                    : `<div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 text-[10px] font-bold border border-gray-200/50 select-none">Sin foto</div>`;

                return `
                    <tr class="hover:bg-stone-50/50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-800">${p.nombre}</td>
                        <td class="px-6 py-4 text-gray-600">$${parseFloat(p.precio).toLocaleString()}</td>
                        <td class="px-6 py-4">${htmlImagen}</td>
                        <td class="px-6 py-4">
                            <button class="text-red-500 hover:text-red-700 font-semibold transition-colors">Eliminar</button>
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
        document.getElementById('modal-producto').classList.add('hidden');
        e.target.reset();
        cargarProductos(formData.get('categoria_id')); 
    } else {
        alert("Error del servidor: " + data.message);
    }
}

async function guardarCategoria(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const res = await fetch('../controllers/api_categorias.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            alert('Categoría guardada con éxito.');
            document.getElementById('modal-categoria').classList.add('hidden');
            form.reset();
            
            // Refresca los botones y el select dinámicamente sin recargar la página
            await cargarCategorias(); 
        } else {
            alert('Error al guardar la categoría: ' + (data.message || 'Error desconocido'));
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Ocurrió un error de red al intentar guardar.');
    }
}
