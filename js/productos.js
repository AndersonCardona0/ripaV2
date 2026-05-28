document.addEventListener('DOMContentLoaded', async () => {
    await cargarCategorias();
    cargarProductos(1); // Carga por defecto la categoría 1
});

async function cargarCategorias() {
    const res = await fetch('../controllers/api_categorias.php');
    const json = await res.json();
    
    const contenedor = document.getElementById('contenedor-categorias');
    const select = document.getElementById('select-categorias');

    // Botones de pestañas
    contenedor.innerHTML = json.data.map(cat => `
        <button onclick="cargarProductos(${cat.id})" class="px-4 py-2 bg-white rounded-full border shadow-sm hover:border-[#BC5F40]">
            ${cat.nombre}
        </button>
    `).join('');

    // Opciones del select en el modal
    select.innerHTML = json.data.map(cat => `
        <option value="${cat.id}">${cat.nombre}</option>
    `).join('');
}

async function cargarProductos(catId) {
    const res = await fetch(`../controllers/api_productos.php?categoria=${catId}`);
    const data = await res.json();
    
    const tbody = document.getElementById('tabla-productos');
    tbody.innerHTML = data.data.map(p => `
        <tr>
            <td class="px-6 py-4 font-semibold">${p.nombre}</td>
            <td class="px-6 py-4">$${parseFloat(p.precio).toLocaleString()}</td>
            <td class="px-6 py-4"><button class="text-red-500">Eliminar</button></td>
        </tr>
    `).join('');
}

// async function guardarProducto(e) {
//     e.preventDefault();
//     const formData = new FormData(e.target);
//     await fetch('../controllers/api_productos.php', { method: 'POST', body: formData });
    
//     document.getElementById('modal-producto').classList.add('hidden');
//     e.target.reset();
//     cargarProductos(formData.get('categoria_id')); // Recarga la categoría que acabas de editar
// }
async function guardarProducto(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // DEBUG: Mira en consola (F12) qué datos se están enviando
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    const res = await fetch('../controllers/api_productos.php', { 
        method: 'POST', 
        body: formData 
    });
    
    const data = await res.json();
    
    if (data.status === 'success') {
        document.getElementById('modal-producto').classList.add('hidden');
        e.target.reset();
        // Recargar la categoría que seleccionaste en el select
        cargarProductos(formData.get('categoria_id')); 
    } else {
        alert("Error del servidor: " + data.message);
    }
}