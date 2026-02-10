document.getElementById('formGasto').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('../php/guardar_gasto.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(mensaje => {
        if (mensaje.includes("✅")) {
            // Limpiar el formulario
            document.getElementById('formGasto').reset();
            // Actualizar la lista y el total
            actualizarTablaGastos();
            alert(mensaje);
        } else {
            alert(mensaje);
        }
    })
    .catch(error => console.error('Error:', error));
});

function actualizarTablaGastos() {
    fetch('../php/obtener_gastos.php')
    .then(res => res.text())
    .then(html => {
        document.getElementById('cuerpoTablaGastos').innerHTML = html;
    });
}

function eliminarGasto(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este gasto?')) {
        fetch(`../php/eliminar_gasto.php?id=${id}`)
        .then(res => res.text())
        .then(mensaje => {
            alert(mensaje);
            actualizarTablaGastos();
        });
    }
}
function abrirEditarGasto(gasto) {
    document.getElementById('edit_id').value = gasto.id;
    document.getElementById('edit_concepto').value = gasto.concepto;
    document.getElementById('edit_monto').value = gasto.monto;
    document.getElementById('modalEditarGasto').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modalEditarGasto').style.display = 'none';
}

document.getElementById('formEditarGasto').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../php/actualizar_gasto.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(mensaje => {
        alert(mensaje);
        cerrarModal();
        actualizarTablaGastos(); // La función que ya tenías
    });
});