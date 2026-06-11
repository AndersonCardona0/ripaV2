
function abrirConfirmacionPago() {
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const impuesto = subtotal * 0.08;
    const total = subtotal + impuesto;
    const mesaId = parseInt(document.body.getAttribute('data-mesa-id') || '1');

    if (total <= 0) {
        alert('La orden está vacía.');
        return;
    }

    abrirModalConfirmacion({
        iconBg: 'bg-green-50',
        iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>`,
        titulo: '¿Procesar pago?',
        descripcion: 'Se cerrará la comanda activa y se registrará el pago correspondiente.',
        previewVisual: `<div class="w-full h-full bg-amber-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>`,
        nombre: `Mesa ${String(mesaId).padStart(2, '0')}`,
        detalle: `Total: $${total.toFixed(2)}`,
        labelConfirmar: 'Ir a Pago',
        onConfirmar: () => openPaymentModal()
    });
}

function openPaymentModal() {
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const impuesto = subtotal * 0.08;
    const total = subtotal + impuesto;

    document.getElementById('m-subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('m-impuesto').innerText = `$${impuesto.toFixed(2)}`;
    document.getElementById('m-total').innerText = `$${total.toFixed(2)}`;
    document.getElementById('m-total-btn').innerText = `$${total.toFixed(2)}`;

    const contenedor = document.getElementById('modal-resumen-items');
    contenedor.innerHTML = carrito.map(item => `
        <div class="flex justify-between text-sm">
            <span>${item.nombre} <span class="text-gray-400">x ${item.cantidad}</span></span>
            <span class="font-bold">$${(item.precio * item.cantidad).toFixed(2)}</span>
        </div>
    `).join('');

    document.getElementById('modal-pago').classList.remove('hidden');
}

// Lógica para cerrar el modal
document.getElementById('modal-pago')?.addEventListener('click', (e) => {
    if (e.target.id === 'modal-pago') e.target.classList.add('hidden');
});


function procesarPagoFinal() {

    const totalRaw = document.getElementById('m-total').innerText;
    const subtotalRaw = document.getElementById('m-subtotal').innerText;
    const impuestoRaw = document.getElementById('m-impuesto').innerText;
    const mesaActiva = document.body.getAttribute('data-mesa-id');

    const datosPago = {
        mesa_id: mesaActiva, 
        subtotal: parseFloat(subtotalRaw.replace('$', '')),
        impuesto: parseFloat(impuestoRaw.replace('$', '')),
        total: parseFloat(totalRaw.replace('$', '')),
        metodo: 'tarjeta' // Puedes cambiar esto si agregas selectores para efectivo/digital
    };

    fetch('../controllers/api_pagar_mesa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datosPago)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('modal-pago').classList.add('hidden');
            window.location.href = 'index.php';
        } else {
            alert('Error al procesar el pago: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hubo un error de conexión al procesar el pago.');
    });
}
