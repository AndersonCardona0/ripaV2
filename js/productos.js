let ws;

function conectarWebSocket() {
    // Nos conectamos al puerto 8080 del servidor local
    const serverIp = window.location.hostname; // Esto hace que funcione tanto en localhost como en producción
    ws = new WebSocket(`ws://${serverIp}:8080`);

    ws.onopen = () => {
        console.log("🔌 Conectado exitosamente al servidor de Avisos en Tiempo Real");
    };

    ws.onmessage = async (event) => {
        console.log("📩 Mensaje recibido del servidor WebSocket:", event.data);
        if (event.data === 'refrescar_avisos') {
            // Reutilizamos tu excelente función sin alterar su lógica
            await refrescarListaAvisos();
        }
    };

    ws.onclose = () => {
        console.warn("⚠️ Servidor WebSocket desconectado. Intentando reconexión en 5 segundos...");
        setTimeout(conectarWebSocket, 5000); // Reconexión automática
    };

    ws.onerror = (error) => {
        console.error("❌ Error en WebSocket:", error);
    };
}

document.addEventListener('DOMContentLoaded', async () => {

    conectarWebSocket(); // Iniciamos la conexión WebSocket al cargar la página

    // 1. Lógica inicial de carga
    await cargarCategorias();
    cargarProductos(1);
    await refrescarListaAvisos();

    const formAviso = document.getElementById('form-crear-aviso');
    const modal = document.getElementById('modal-avisos');

    if (formAviso) {
        formAviso.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log("Sistema de avisos: Evento submit detectado.");

            const formData = new FormData(formAviso);

            try {
                console.log("Enviando datos al servidor...");
                const response = await fetch('/controllers/api_guardar_aviso.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();
                console.log("DEBUG: Respuesta del servidor:", data);

                if (data.status === 'success') {
                    console.log("⚙️ Debug: Estado success...");
                    alert(data.message);
                    formAviso.reset();
                    if (modal) modal.classList.add('hidden');
                
                    await refrescarListaAvisos(); 


                } else {
                    console.warn("⚠️ Debug: Estado no success..., respuesta: ", data);
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error al conectar:', error);
                alert('Hubo un problema al conectar con el servidor.');
            }
        });
    }

    const toggleButton = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar');



    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', () => {
            if (sidebar.classList.contains('w-0')) {
                sidebar.classList.remove('w-0');
                sidebar.classList.add('w-64');
                localStorage.setItem('sidebarState', 'open');
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-0');
                localStorage.setItem('sidebarState', 'closed');
            }
        });
    }
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

        console.log("DEBUG: Estructura del primer aviso:", result.data[0]);
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

async function cargarProductos(catId) {
    try {
        // 1. PRIMERO: Hacemos la petición al servidor (independiente del DOM)
        const res = await fetch(`../controllers/api_productos.php?categoria=${catId}`);
        if (!res.ok) throw new Error("Error al conectar con la API");
        const data = await res.json();
        
        // 2. DESPUÉS: Buscamos el elemento y dibujamos solo si existe
        const tbody = document.getElementById('tabla-productos');
        
        if (tbody) {
            tbody.innerHTML = data.data.map(p => `
                <tr>
                    <td class="px-6 py-4 font-semibold">${p.nombre}</td>
                    <td class="px-6 py-4">$${parseFloat(p.precio).toLocaleString()}</td>
                    <td class="px-6 py-4"><button class="text-red-500">Eliminar</button></td>
                </tr>
            `).join('');
        } else {
            
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