// 1. SISTEMA DE COMUNICACIÓN EN TIEMPO REAL (WEBSOCKET GLOBAL)

let ws;

function conectarWebSocket() {
    const serverIp = window.location.hostname; 
    ws = new WebSocket(`ws://${serverIp}:8080`);

    ws.onopen = () => {
        console.log("🔌 Conectado exitosamente al servidor de Avisos en Tiempo Real (Global)");
    };

    ws.onmessage = async (event) => {
        console.log("Mensaje recibido del servidor WebSocket:", event.data);
        
        if (event.data === 'refrescar_avisos') {
            if (typeof refrescarListaAvisos === 'function') {
                console.log("🔄 Refrescando lista de avisos en esta sección...");
                await refrescarListaAvisos();
            } else {
                console.log("ℹAviso recibido. No hay lista que refrescar en esta pantalla, pero el sistema está al día.");
            }
        }
    };

    ws.onclose = () => {
        console.warn("⚠️ Servidor WebSocket desconectado. Intentando reconexión en 5 segundos...");
        setTimeout(conectarWebSocket, 5000); 
    };

    ws.onerror = (error) => {
        console.error("❌ Error en WebSocket:", error);
    };
}


// 2. LÓGICA CENTRAL DEL DOM (AL CARGAR LA PÁGINA)

document.addEventListener('DOMContentLoaded', () => {
    
    // Encendemos el WebSocket inmediatamente de forma global
    conectarWebSocket();

    // --- Lógica específica de Mesas ---
    const contenedor = document.getElementById('grid-mesas');

    if (contenedor) { 
        function cargarMesas() {
            fetch('/controllers/api_mesas.php')
                .then(res => res.json())
                .then(res => {
                    contenedor.innerHTML = ''; 
                    
                    const mesasAgrupadas = {};
                    let disponibles = 0;
                    let ocupadas = 0;

                    res.data.forEach(mesa => {
                        const num = mesa.numero_mesa;
                        if (!mesasAgrupadas[num]) {
                            mesasAgrupadas[num] = { ...mesa, total_balance: parseFloat(mesa.total_balance) || 0 };
                        } else {
                            mesasAgrupadas[num].total_balance += parseFloat(mesa.total_balance) || 0;
                        }
                    });
                    
                    Object.values(mesasAgrupadas).forEach(mesa => {
                        if (mesa.estado === 'libre') disponibles++;
                        else ocupadas++;

                        let tarjetaHtml = '';
                        
                        if (mesa.estado === 'libre') {
                            tarjetaHtml = `
                                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between h-48 transition hover:shadow-md cursor-pointer" onclick="irAMesa(${mesa.numero_mesa})">
                                    <div class="mesa-header">
                                        <h3 class="text-xl font-bold text-gray-800">Mesa ${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
                                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Disponible</span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Mesa lista para recibir clientes.</div>
                                    <div class="flex justify-end mt-4">
                                        <button class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">+</button>
                                    </div>
                                </div>`;
                        } else {
                            if (res.user_role === 'administrador') {
                                btnPagar = `
                                    <button type="button" 
                                            onclick="event.stopPropagation(); openConfirmModal('Cobrar Mesa', '¿Confirmar pago de la mesa T-${mesa.numero_mesa.toString().padStart(2, '0')}?', '/controllers/pagar_mesa.php?id=${mesa.numero_mesa}', false)"
                                            class="mt-3 w-full bg-green-600 text-white font-bold p-2 rounded-xl hover:bg-green-700 transition">
                                        Pagar Mesa
                                    </button>`;
                            }
                            tarjetaHtml = `
                                <div class="bg-white rounded-3xl p-6 shadow-sm border-2 border-secondary flex flex-col justify-between h-48 cursor-pointer" onclick="irAMesa(${mesa.numero_mesa})">
                                    <div class="mesa-header">
                                        <h3 class="text-xl font-bold text-gray-800">Mesa ${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
                                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">Ocupada</span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Consumiendo en mesa...</div>
                                    <div class="mt-4 flex justify-between items-end">
                                        <div>
                                            <span class="text-xs text-gray-400 block">Balance Actual</span>
                                            <span class="text-xl font-bold text-gray-900">$${mesa.total_balance.toFixed(2)}</span>
                                        </div>
                                        <div class="flex -space-x-2">
                                            <div class="w-7 h-7 bg-amber-700 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white font-bold"></div>
                                        </div>
                                    </div>
                                </div>`;
                        }
                        contenedor.innerHTML += tarjetaHtml;
                    });

                    const countAvail = document.getElementById('count-available');
                    const countOcc = document.getElementById('count-occupied');
                    if (countAvail) countAvail.innerText = disponibles;
                    if (countOcc) countOcc.innerText = ocupadas;
                })
                .catch(err => console.error("Error cargando mesas:", err));
        }

        cargarMesas();
        setInterval(cargarMesas, 4000);
    }

    // --- PROCESAMIENTO GLOBAL DE AVISOS EN SEGUNDO PLANO ---
    const formAvisoGlobal = document.getElementById('form-crear-aviso');
    const modalAvisosGlobal = document.getElementById('modal-avisos');

    if (formAvisoGlobal) {
        formAvisoGlobal.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            console.log("Sistema global: Enviando aviso en segundo plano...");

            const formData = new FormData(formAvisoGlobal);

            try {
                const response = await fetch('/controllers/api_guardar_aviso.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                if (data.status === 'success') {
                    formAvisoGlobal.reset(); 
                    if (modalAvisosGlobal) modalAvisosGlobal.classList.add('hidden'); 
                    
                    if (typeof refrescarListaAvisos === 'function') {
                        await refrescarListaAvisos();
                    }
                } else {
                    console.warn("⚠️ Error en el servidor al guardar aviso:", data.message);
                }
            } catch (error) {
                console.error('❌ Error en la petición global de avisos:', error);
            }
        });
    }
});


// 3. LOGICA DE MODALES UNIVERSALES Y NAVEGACIÓN (GLOBALES)

window.openConfirmModal = function(title, message, url, isDestructive = true) {
    const modal = document.getElementById('modal-confirm');
    document.getElementById('confirm-title').innerText = title;
    document.getElementById('confirm-msg').innerText = message;
    
    const btn = document.getElementById('confirm-action-btn');
    btn.href = url;
    
    if (!isDestructive) {
        btn.classList.remove('bg-red-500', 'hover:bg-red-600');
        btn.classList.add('bg-[#BC5F40]', 'hover:bg-amber-800');
    } else {
        btn.classList.add('bg-red-500', 'hover:bg-red-600');
        btn.classList.remove('bg-[#BC5F40]', 'hover:bg-amber-800');
    }
    
    modal.classList.remove('hidden');
}

window.closeConfirmModal = function() {
    document.getElementById('modal-confirm').classList.add('hidden');
}

window.irAMesa = function(id) {
    window.location.href = '/views/pedido.php?mesa=' + id;
}

// Lógica de configuración de mesas

// function abrirConfiguracionSalon() {
//     const totalMesasActuales = document.getElementById('grid-mesas').children.length;
//     const input = document.getElementById('input-total-mesas');
    
//     input.value = totalMesasActuales > 0 ? totalMesasActuales : 12; // 12 por defecto si la base está limpia
//     actualizarPrevisualizacion(parseInt(input.value));
    
//     document.getElementById('modal-config-salon').classList.remove('hidden');
// }

// function cambiarCantidadMesas(valor) {
//     const input = document.getElementById('input-total-mesas');
//     let actual = parseInt(input.value) + valor;
//     if (actual >= 1 && actual <= 50) { 
//         input.value = actual;
//         actualizarPrevisualizacion(actual);
//     }
// }

// function actualizarPrevisualizacion(total) {
//     const grid = document.getElementById('previsualizacion-grid');
//     grid.innerHTML = '';
//     for (let i = 1; i <= total; i++) {
//         const box = document.createElement('div');
//         box.className = "w-10 h-10 rounded-xl bg-[#F5EDE3] border border-[#BC5F40]/20 flex items-center justify-center text-xs font-bold text-[#BC5F40] shadow-sm";
//         box.innerText = i;
//         grid.appendChild(box);
//     }
// }

// // Envía la nueva cantidad al controlador del backend
// function guardarConfiguracionMesas() {
//     const nuevoTotal = document.getElementById('input-total-mesas').value;

//     fetch('/controllers/api_actualizar_total_mesas.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ total_mesas: parseInt(nuevoTotal) })
//     })
//     .then(res => res.json())
//     .then(data => {
//         if (data.status === 'success') {
//             document.getElementById('modal-config-salon').classList.add('hidden');
//             if (typeof cargarMesas === 'function') {
//                 cargarMesas(); // Recarga el mapa de mesas principal instantáneamente
//             } else {
//                 window.location.reload(); 
//             }
//         } else {
//             alert('Error al actualizar las mesas: ' + data.message);
//         }
//     })
//     .catch(err => {
//         console.error('Error:', err);
//         alert('Error en la comunicación con el servidor.');
//     });
// }

window.abrirConfiguracionSalon = function() {
    const gridMesas = document.getElementById('grid-mesas');
    // Captura el conteo real basado en los elementos hijos inyectados por la API
    const totalMesasActuales = gridMesas ? gridMesas.children.length : 0;
    const input = document.getElementById('input-total-mesas');
    
    if (input) {
        input.value = totalMesasActuales > 0 ? totalMesasActuales : 12; // 12 por defecto si la base de datos está vacía
        window.actualizarPrevisualizacion(parseInt(input.value));
    }
    
    const modal = document.getElementById('modal-config-salon');
    if (modal) modal.classList.remove('hidden');
};

window.cambiarCantidadMesas = function(valor) {
    const input = document.getElementById('input-total-mesas');
    if (input) {
        let actual = parseInt(input.value) + valor;
        if (actual >= 1 && actual <= 50) { 
            input.value = actual;
            window.actualizarPrevisualizacion(actual);
        }
    }
};

window.actualizarPrevisualizacion = function(total) {
    const gridPrevis = document.getElementById('previsualizacion-grid');
    if (gridPrevis) {
        gridPrevis.innerHTML = '';
        for (let i = 1; i <= total; i++) {
            const box = document.createElement('div');
            box.className = "w-10 h-10 rounded-xl bg-[#F5EDE3] border border-[#BC5F40]/20 flex items-center justify-center text-xs font-bold text-[#BC5F40] shadow-sm animate-inside";
            box.innerText = i;
            gridPrevis.appendChild(box);
        }
    }
};

window.guardarConfiguracionMesas = function() {
    const input = document.getElementById('input-total-mesas');
    if (!input) return;
    
    const nuevoTotal = parseInt(input.value);

    fetch('/controllers/api_actualizar_total_mesas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ total_mesas: nuevoTotal })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Ocultar modal con éxito
            const modal = document.getElementById('modal-config-salon');
            if (modal) modal.classList.add('hidden');
            
            // Refresco asíncrono e inteligente sin recargar la página completa
            if (typeof window.cargarMesas === 'function') {
                window.cargarMesas(); 
            } else {
                window.location.reload(); 
            }
        } else {
            alert('Error al actualizar las mesas: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error en la petición de actualización:', err);
        alert('Error en la comunicación con el servidor.');
    });
};