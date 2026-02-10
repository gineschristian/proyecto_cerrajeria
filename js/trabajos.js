document.addEventListener('DOMContentLoaded', () => {
    const formTrabajo = document.getElementById('formTrabajo');

    formTrabajo.addEventListener('submit', function(e) {
        e.preventDefault();
        let datos = new FormData(this);

        fetch('../php/guardar_trabajo.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.text())
        .then(mensaje => {
            alert(mensaje);
            if (mensaje.includes('✅')) {
                // Si el registro fue exitoso, limpiamos y recargamos
                formTrabajo.reset();
                location.reload(); 
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al conectar con el servidor.');
        });
    });
});

function eliminarTrabajo(id) {
    if (!confirm("¿Seguro que quieres borrar este trabajo? El stock utilizado se devolverá al inventario.")) return;

    const datos = new FormData();
    datos.append('id', id);

    fetch('../php/eliminar_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(res => res.text())
    .then(mensaje => {
        alert(mensaje);
        location.reload();
    });
}

function editarTrabajo(id) {
    // Para la edición simple, podemos usar prompts o un modal. 
    // Lo más crítico suele ser el precio o la descripción.
    const nuevaDesc = prompt("Nueva descripción del trabajo:");
    if (nuevaDesc === null) return;

    const nuevoPrecio = prompt("Nuevo precio cobrado (€):");
    if (nuevoPrecio === null) return;

    const datos = new FormData();
    datos.append('id', id);
    datos.append('descripcion', nuevaDesc);
    datos.append('precio', nuevoPrecio);

    fetch('../php/editar_trabajo_simple.php', {
        method: 'POST',
        body: datos
    })
    .then(res => res.text())
    .then(mensaje => {
        alert(mensaje);
        location.reload();
    });
}
function filtrarTrabajos() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;

    // Recargamos la tabla enviando los parámetros por URL
    fetch(`../php/obtener_trabajos.php?inicio=${inicio}&fin=${fin}`)
    .then(res => res.text())
    .then(html => {
        document.getElementById('cuerpoTablaTrabajos').innerHTML = html;
    });
}
function abrirEditarTrabajo(trabajo) {
    document.getElementById('edit_trabajo_id').value = trabajo.id;
    document.getElementById('edit_trabajo_cliente').value = trabajo.cliente;
    document.getElementById('edit_trabajo_precio').value = trabajo.precio_total;
    document.getElementById('modalEditarTrabajo').style.display = 'flex';
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}
// Función para abrir el modal y rellenar los campos
function abrirEditarTrabajo(trabajo) {
    document.getElementById('edit_trabajo_id').value = trabajo.id;
    document.getElementById('edit_trabajo_cliente').value = trabajo.cliente;
    document.getElementById('edit_trabajo_desc').value = trabajo.descripcion;
    document.getElementById('edit_trabajo_precio').value = trabajo.precio_total;
    document.getElementById('edit_trabajo_estado').value = trabajo.estado;
    
    document.getElementById('modalEditarTrabajo').style.display = 'flex';
}

function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Manejar el envío del formulario de edición
document.getElementById('formEditarTrabajo').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../php/actualizar_trabajo.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(mensaje => {
        alert(mensaje);
        if (mensaje.includes("✅")) {
            cerrarModal('modalEditarTrabajo');
            location.reload(); // Recargamos para ver los cambios de estado
        }
    });
});