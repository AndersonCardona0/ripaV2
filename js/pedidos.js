

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

    fetch('../controllers/pagar_mesa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datosPago)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(`Procesando pago de ${totalRaw}... ¡Pago Exitoso!`);
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