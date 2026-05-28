document.addEventListener('DOMContentLoaded', () => {
    
    //Lógica del Sidebar 
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
//Lógica específica de Mesas 
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
                            //Si es admin, llenamos la variable con el botón
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

                    // Actualizamos contadores si existen en el DOM
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

});

// Lógica del Modal Universal 
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

        //Función global de navegación 
    window.irAMesa = function(id) {
        window.location.href = '/views/pedido.php?mesa=' + id;
    }