document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('grid-mesas');
    if (!contenedor) return;

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
                            <div class="bg-white rounded-3xl p-6 border border-stone-100 shadow-sm flex flex-col cursor-pointer hover:shadow-md transition-shadow"
                                 onclick="irAMesa(${mesa.numero_mesa})">

                                <div class="flex justify-between items-start mb-5">
                                    <span class="text-5xl font-thin text-stone-300 leading-none tracking-tight">
                                        ${mesa.numero_mesa.toString().padStart(2, '0')}
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-emerald-50 text-emerald-600 text-xs font-semibold px-3 py-1.5 rounded-full">
                                        <span class="w-2 h-2 bg-emerald-400 rounded-full inline-block"></span>
                                        Disponible
                                    </span>
                                </div>

                                <div class="flex-1">
                                    <p class="text-xs uppercase tracking-wider text-stone-300 font-semibold mb-0.5">Mesero</p>
                                    <p class="text-base text-stone-300 font-medium">Sin asignar</p>
                                </div>

                                <div class="border-t border-stone-100 my-4"></div>

                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-stone-300 font-medium">Lista para atender</span>
                                    <button class="w-8 h-8 bg-[#BC5F40] text-white rounded-full flex items-center justify-center text-lg font-bold hover:bg-[#a04e35] transition-colors shadow-sm">
                                        +
                                    </button>
                                </div>
                            </div>`;
                    } else {
                        tarjetaHtml = `
                            <div class="bg-white rounded-3xl p-6 border border-stone-100 shadow-sm flex flex-col cursor-pointer hover:shadow-md transition-shadow"
                                 onclick="irAMesa(${mesa.numero_mesa})">

                                <div class="flex justify-between items-start mb-5">
                                    <span class="text-5xl font-thin text-stone-700 leading-none tracking-tight">
                                        ${mesa.numero_mesa.toString().padStart(2, '0')}
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-[#BC5F40]/10 text-[#BC5F40] text-xs font-semibold px-3 py-1.5 rounded-full">
                                        <span class="w-2 h-2 bg-[#BC5F40] rounded-full inline-block"></span>
                                        Ocupada
                                    </span>
                                </div>

                                <div class="flex-1">
                                    <p class="text-xs uppercase tracking-wider text-stone-400 font-semibold mb-0.5">Mesero</p>
                                    <p class="text-base text-stone-800 font-medium">${mesa.mesero_nombre ?? 'Sin asignar'}</p>
                                </div>

                                <div class="border-t border-stone-100 my-4"></div>

                                <div class="flex justify-between items-center text-stone-400">
                                    <span class="flex items-center gap-1.5 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        ${mesa.tiempo_ocupada ?? '—'}
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#BC5F40]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <line x1="8" y1="3" x2="8" y2="21" stroke-linecap="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v5a3 3 0 006 0V3"/>
                                        <line x1="16" y1="3" x2="16" y2="21" stroke-linecap="round"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 3c1.5 0 3 1 3 3s-1.5 2.5-3 3.5"/>
                                    </svg>
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

    window.cargarMesas = cargarMesas;
    cargarMesas();
    setInterval(cargarMesas, 4000);
});

window.abrirConfiguracionSalon = function() {
    const gridMesas = document.getElementById('grid-mesas');
    const totalMesasActuales = gridMesas ? gridMesas.children.length : 0;
    const input = document.getElementById('input-total-mesas');

    if (input) {
        input.value = totalMesasActuales > 0 ? totalMesasActuales : 12;
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
            const modal = document.getElementById('modal-config-salon');
            if (modal) modal.classList.add('hidden');
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
