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
                    const esDisponible = mesa.estado === 'disponible' || mesa.estado === 'libre';
                    if (esDisponible) disponibles++;
                    else ocupadas++;

                    let tarjetaHtml = '';

                    if (esDisponible) {
                        tarjetaHtml = `
                            <div class="bg-white rounded-3xl p-6 border border-stone-100 shadow-sm flex flex-col cursor-pointer hover:shadow-md transition-shadow"
                                 onclick="irAMesa(${mesa.id}, ${mesa.numero_mesa}, true)">

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
                                 onclick="irAMesa(${mesa.id}, ${mesa.numero_mesa}, true)">

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
                                    <p class="text-base text-stone-800 font-medium mb-3">${mesa.mesero_nombre || 'Sin asignar'}</p>
                                    <p class="text-2xl font-semibold text-stone-800">$${Number(mesa.total_balance).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
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

// ── Flujo de apertura de mesa con PIN ────────────────────────────────────────

const pinState = {
    mesaId:    null,
    numeroMesa: null,
    pin:       '',
    maxLength: 6,
    cargando:  false,
    bloqueado: false
};

window.irAMesa = function (mesaId, numeroMesa) {
    abrirModalPin(mesaId, numeroMesa);
};

function abrirModalPin(mesaId, numeroMesa) {
    pinState.mesaId    = mesaId;
    pinState.numeroMesa = numeroMesa;
    pinState.pin       = '';
    pinState.cargando  = false;
    pinState.bloqueado = false;

    actualizarDisplayPin();
    ocultarErrorPin();

    const modal = document.getElementById('modal-pin');
    if (modal) modal.classList.remove('hidden');
}

window.cerrarModalPin = function () {
    const modal = document.getElementById('modal-pin');
    if (modal) modal.classList.add('hidden');
    pinState.mesaId    = null;
    pinState.numeroMesa = null;
    pinState.pin       = '';
    pinState.bloqueado = false;
};

window.pinKey = function (key) {
    if (pinState.cargando || pinState.bloqueado) return;
    ocultarErrorPin();

    if (key === 'del') {
        pinState.pin = pinState.pin.slice(0, -1);
    } else if (pinState.pin.length < pinState.maxLength) {
        pinState.pin += key;
    }

    actualizarDisplayPin();
};

function actualizarDisplayPin() {
    const display = document.getElementById('pin-display');
    if (display) {
        display.textContent = '*'.repeat(pinState.pin.length);
        display.style.color = '';
    }
}

function ocultarErrorPin() {
    const err = document.getElementById('pin-error');
    if (err) err.style.opacity = '0';
}

function mostrarErrorPin(msg) {
    pinState.bloqueado = true;

    const err = document.getElementById('pin-error');
    if (err) {
        err.textContent   = msg || 'PIN incorrecto. Intenta de nuevo.';
        err.style.opacity = '1';
    }

    const display = document.getElementById('pin-display');
    if (display) display.style.color = '#ef4444';

    setTimeout(() => {
        pinState.pin       = '';
        pinState.bloqueado = false;
        actualizarDisplayPin();
    }, 700);
}

window.pinConfirmar = async function () {
    if (pinState.pin.length < 4 || pinState.cargando || pinState.bloqueado) return;

    pinState.cargando  = true;
    const numeroMesa   = pinState.numeroMesa;
    const confirmBtn   = document.getElementById('pin-confirm-btn');

    if (confirmBtn) {
        confirmBtn.disabled     = true;
        confirmBtn.textContent  = 'Verificando...';
    }

    try {
        const res  = await fetch('/controllers/api_mesas.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ accion: 'abrir_mesa', mesa_id: pinState.mesaId, pin: pinState.pin })
        });
        const data = await res.json();

        if (data.status === 'success') {
            cerrarModalPin();
            window.location.href = `pedido.php?mesa=${numeroMesa}`;
        } else if (data.status === 'pin_incorrecto') {
            mostrarErrorPin('PIN incorrecto. Intenta de nuevo.');
        } else {
            mostrarErrorPin(data.message || 'Error inesperado.');
        }
    } catch (e) {
        mostrarErrorPin('Error de conexión. Intenta de nuevo.');
    } finally {
        pinState.cargando = false;
        if (confirmBtn) {
            confirmBtn.disabled    = false;
            confirmBtn.textContent = 'Confirmar Apertura';
        }
    }
};

// Soporte para teclado físico cuando el modal está visible
document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('modal-pin');
    if (!modal || modal.classList.contains('hidden')) return;

    if (e.key >= '0' && e.key <= '9') {
        pinKey(e.key);
    } else if (e.key === 'Backspace') {
        e.preventDefault();
        pinKey('del');
    } else if (e.key === 'Enter') {
        pinConfirmar();
    } else if (e.key === 'Escape') {
        cerrarModalPin();
    }
});

// ─────────────────────────────────────────────────────────────────────────────

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
