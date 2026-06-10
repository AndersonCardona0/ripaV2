let ws;

function conectarWebSocket() {
    const serverIp = window.location.hostname;
    ws = new WebSocket(`ws://${serverIp}:8080`);

    ws.onopen = () => {};

    ws.onmessage = async (event) => {
        if (event.data === 'refrescar_avisos') {
            await refrescarListaAvisos();
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

async function refrescarListaAvisos() {
    const contenedor = document.getElementById('contenedor-avisos');
    if (!contenedor) return;
    try {
        const response = await fetch('../controllers/api_leer_avisos.php?t=' + new Date().getTime());
        const result = await response.json();

        if (!response.ok) throw new Error("Error en la conexión con el servidor");

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
                return `
                    <div class="flex justify-between items-center border-b p-3 border-gray-100 bg-white">
                        <div>
                            <p class="font-bold text-gray-800">${aviso.titulo}</p>
                            <p class="text-gray-600 text-sm">${aviso.mensaje}</p>
                        </div>
                        <div>${botonEliminar}</div>
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

document.addEventListener('DOMContentLoaded', () => {
    conectarWebSocket();
    refrescarListaAvisos();

    const formAvisoGlobal = document.getElementById('form-crear-aviso');
    const modalAvisosGlobal = document.getElementById('modal-avisos');

    if (formAvisoGlobal) {
        formAvisoGlobal.addEventListener('submit', async (e) => {
            e.preventDefault();

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
                    await refrescarListaAvisos();
                } else {
                    console.warn("⚠️ Error en el servidor al guardar aviso:", data.message);
                }
            } catch (error) {
                console.error('❌ Error en la petición global de avisos:', error);
            }
        });
    }
});

// ── Modal de Confirmación Global ─────────────────────────────────────────────
let _mcCallback = null;

window.abrirModalConfirmacion = function({ iconHtml, iconBg, titulo, descripcion, previewVisual, nombre, detalle, labelConfirmar = 'Confirmar', onConfirmar }) {
    const iconEl = document.getElementById('mc-icon');
    iconEl.className = `w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 ${iconBg}`;
    iconEl.innerHTML = iconHtml;

    document.getElementById('mc-titulo').innerText = titulo;
    document.getElementById('mc-descripcion').innerText = descripcion;
    document.getElementById('mc-preview-visual').innerHTML = previewVisual;
    document.getElementById('mc-preview-nombre').innerText = nombre;
    document.getElementById('mc-preview-detalle').innerText = detalle;
    document.getElementById('mc-btn-confirmar').innerText = labelConfirmar;

    _mcCallback = onConfirmar;
    document.getElementById('modal-confirmacion').classList.remove('hidden');
};

window.cerrarModalConfirmacion = function() {
    document.getElementById('modal-confirmacion').classList.add('hidden');
    _mcCallback = null;
};

window.ejecutarConfirmacion = function() {
    if (typeof _mcCallback === 'function') _mcCallback();
    cerrarModalConfirmacion();
};

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
};

window.closeConfirmModal = function() {
    document.getElementById('modal-confirm').classList.add('hidden');
};

// irAMesa está definida en mesas.js con el flujo completo de PIN
