// document.addEventListener('DOMContentLoaded', () => {
    
//     //Lógica del Sidebar 
//     const sidebar = document.getElementById('sidebar');
//     const toggleBtn = document.getElementById('toggle-sidebar');

//     if (sidebar && toggleBtn) {
//         const savedState = localStorage.getItem('sidebarState') || 'open';
        
//         if (savedState === 'closed') {
//             sidebar.classList.remove('w-64');
//             sidebar.classList.add('w-0');
//         } else {
//             sidebar.classList.remove('w-0');
//             sidebar.classList.add('w-64');
//         }

//         setTimeout(() => {
//             sidebar.classList.add('transition-all', 'duration-300');
//         }, 100);

//         toggleBtn.addEventListener('click', () => {
//             const isClosed = sidebar.classList.contains('w-0');
//             if (isClosed) {
//                 sidebar.classList.remove('w-0');
//                 sidebar.classList.add('w-64');
//                 localStorage.setItem('sidebarState', 'open');
//             } else {
//                 sidebar.classList.remove('w-64');
//                 sidebar.classList.add('w-0');
//                 localStorage.setItem('sidebarState', 'closed');
//             }
//         });
//     }
// //Lógica específica de Mesas 
//     const contenedor = document.getElementById('grid-mesas');

//     if (contenedor) { 
//         function cargarMesas() {
//             fetch('/controllers/api_mesas.php')
//                 .then(res => res.json())
//                 .then(res => {
//                     contenedor.innerHTML = ''; 
                    
//                     const mesasAgrupadas = {};
//                     let disponibles = 0;
//                     let ocupadas = 0;

//                     res.data.forEach(mesa => {
//                         const num = mesa.numero_mesa;
//                         if (!mesasAgrupadas[num]) {
//                             mesasAgrupadas[num] = { ...mesa, total_balance: parseFloat(mesa.total_balance) || 0 };
//                         } else {
//                             mesasAgrupadas[num].total_balance += parseFloat(mesa.total_balance) || 0;
//                         }
//                     });
                    
//                     Object.values(mesasAgrupadas).forEach(mesa => {
//                         if (mesa.estado === 'libre') disponibles++;
//                         else ocupadas++;

//                         let tarjetaHtml = '';
                        
//                         if (mesa.estado === 'libre') {
//                             tarjetaHtml = `
//                                 <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between h-48 transition hover:shadow-md cursor-pointer" onclick="irAMesa(${mesa.numero_mesa})">
//                                     <div class="mesa-header">
//                                         <h3 class="text-xl font-bold text-gray-800">T-${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
//                                         <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Disponible</span>
//                                     </div>
//                                     <div class="text-xs text-gray-400 mt-2">Mesa lista para recibir clientes.</div>
//                                     <div class="flex justify-end mt-4">
//                                         <button class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">+</button>
//                                     </div>
//                                 </div>`;
//                         } else {
//                             //Si es admin, llenamos la variable con el botón
//                             if (res.user_role === 'administrador') {
//                                 btnPagar = `
//                                     <button type="button" 
//                                             onclick="event.stopPropagation(); openConfirmModal('Cobrar Mesa', '¿Confirmar pago de la mesa T-${mesa.numero_mesa.toString().padStart(2, '0')}?', '/controllers/pagar_mesa.php?id=${mesa.numero_mesa}', false)"
//                                             class="mt-3 w-full bg-green-600 text-white font-bold p-2 rounded-xl hover:bg-green-700 transition">
//                                         Pagar Mesa
//                                     </button>`;
//                             }
//                             tarjetaHtml = `
//                                 <div class="bg-white rounded-3xl p-6 shadow-sm border-2 border-secondary flex flex-col justify-between h-48 cursor-pointer" onclick="irAMesa(${mesa.numero_mesa})">
//                                     <div class="mesa-header">
//                                         <h3 class="text-xl font-bold text-gray-800">T-${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
//                                         <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">Ocupada</span>
//                                     </div>
//                                     <div class="text-xs text-gray-400 mt-2">Consumiendo en mesa...</div>
//                                     <div class="mt-4 flex justify-between items-end">
//                                         <div>
//                                             <span class="text-xs text-gray-400 block">Balance Actual</span>
//                                             <span class="text-xl font-bold text-gray-900">$${mesa.total_balance.toFixed(2)}</span>
//                                         </div>
//                                         <div class="flex -space-x-2">
//                                             <div class="w-7 h-7 bg-amber-700 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white font-bold">☕</div>
//                                         </div>
//                                     </div>
//                                 </div>`;
//                         }
//                         contenedor.innerHTML += tarjetaHtml;
//                     });

//                     // Actualizamos contadores si existen en el DOM
//                     const countAvail = document.getElementById('count-available');
//                     const countOcc = document.getElementById('count-occupied');
//                     if (countAvail) countAvail.innerText = disponibles;
//                     if (countOcc) countOcc.innerText = ocupadas;
//                 })
//                 .catch(err => console.error("Error cargando mesas:", err));
//         }

//         cargarMesas();
//         setInterval(cargarMesas, 4000);
//     }

// });

// // Lógica del Modal Universal 
//     window.openConfirmModal = function(title, message, url, isDestructive = true) {
//         const modal = document.getElementById('modal-confirm');
//         document.getElementById('confirm-title').innerText = title;
//         document.getElementById('confirm-msg').innerText = message;
        
//         const btn = document.getElementById('confirm-action-btn');
//         btn.href = url;
        
//         if (!isDestructive) {
//             btn.classList.remove('bg-red-500', 'hover:bg-red-600');
//             btn.classList.add('bg-[#BC5F40]', 'hover:bg-amber-800');
//         } else {
//             btn.classList.add('bg-red-500', 'hover:bg-red-600');
//             btn.classList.remove('bg-[#BC5F40]', 'hover:bg-amber-800');
//         }
        
//         modal.classList.remove('hidden');
//     }

//     window.closeConfirmModal = function() {
//         document.getElementById('modal-confirm').classList.add('hidden');
//     }

//         //Función global de navegación 
//     window.irAMesa = function(id) {
//         window.location.href = '/views/pedido.php?mesa=' + id;
//     }

//     let ws;

// function conectarWebSocket() {
//     // Nos conectamos al puerto 8080 del servidor local
//     const serverIp = window.location.hostname; // Esto hace que funcione tanto en localhost como en producción
//     ws = new WebSocket(`ws://${serverIp}:8080`);

//     ws.onopen = () => {
//         console.log("🔌 Conectado exitosamente al servidor de Avisos en Tiempo Real");
//     };

//     ws.onmessage = async (event) => {
//         console.log("📩 Mensaje recibido del servidor WebSocket:", event.data);
//         if (event.data === 'refrescar_avisos') {
//             // Reutilizamos tu excelente función sin alterar su lógica
//             await refrescarListaAvisos();
//         }
//     };

//     ws.onclose = () => {
//         console.warn("⚠️ Servidor WebSocket desconectado. Intentando reconexión en 5 segundos...");
//         setTimeout(conectarWebSocket, 5000); // Reconexión automática
//     };

//     ws.onerror = (error) => {
//         console.error("❌ Error en WebSocket:", error);
//     };
// }

// ========================================================
// 1. SISTEMA DE COMUNICACIÓN EN TIEMPO REAL (WEBSOCKET GLOBAL)
// ========================================================
let ws;

function conectarWebSocket() {
    const serverIp = window.location.hostname; 
    ws = new WebSocket(`ws://${serverIp}:8080`);

    ws.onopen = () => {
        console.log("🔌 Conectado exitosamente al servidor de Avisos en Tiempo Real (Global)");
    };

    ws.onmessage = async (event) => {
        console.log("📩 Mensaje recibido del servidor WebSocket:", event.data);
        
        if (event.data === 'refrescar_avisos') {
            // DETECCIÓN INTELIGENTE (Para que no se rompa en Mesas u otras vistas)
            if (typeof refrescarListaAvisos === 'function') {
                console.log("🔄 Refrescando lista de avisos en esta sección...");
                await refrescarListaAvisos();
            } else {
                console.log("ℹ️ Aviso recibido. No hay lista que refrescar en esta pantalla, pero el sistema está al día.");
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

// ========================================================
// 2. LÓGICA CENTRAL DEL DOM (AL CARGAR LA PÁGINA)
// ========================================================
document.addEventListener('DOMContentLoaded', () => {
    
    // 🔥 CORREGIDO: Encendemos el WebSocket inmediatamente de forma global
    conectarWebSocket();
    
    // --- Lógica del Sidebar ---
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-sidebar');

    if (sidebar && toggleBtn) {
        const savedState = localStorage.getItem('sidebarState') || 'open';
        
        if (savedState === 'closed') {
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-0');
        } else {
            sidebar.classList.remove('w-0');
            sidebar.classList.add('w-64');
        }

        setTimeout(() => {
            sidebar.classList.add('transition-all', 'duration-300');
        }, 100);

        toggleBtn.addEventListener('click', () => {
            const isClosed = sidebar.classList.contains('w-0');
            if (isClosed) {
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
                                        <h3 class="text-xl font-bold text-gray-800">T-${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
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
                                        <h3 class="text-xl font-bold text-gray-800">T-${mesa.numero_mesa.toString().padStart(2, '0')}</h3>
                                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">Ocupada</span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">Consumiendo en mesa...</div>
                                    <div class="mt-4 flex justify-between items-end">
                                        <div>
                                            <span class="text-xs text-gray-400 block">Balance Actual</span>
                                            <span class="text-xl font-bold text-gray-900">$${mesa.total_balance.toFixed(2)}</span>
                                        </div>
                                        <div class="flex -space-x-2">
                                            <div class="w-7 h-7 bg-amber-700 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white font-bold">☕</div>
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
            e.preventDefault(); // 🚫 Evita recargas
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
                    formAvisoGlobal.reset(); // Limpia los campos
                    if (modalAvisosGlobal) modalAvisosGlobal.classList.add('hidden'); // Cierra el modal solo
                    
                    // Si la función de refrescar existe en esta pantalla (como el Dashboard), la ejecuta
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

// ========================================================
// 3. LOGICA DE MODALES UNIVERSALES Y NAVEGACIÓN (GLOBALES)
// ========================================================
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