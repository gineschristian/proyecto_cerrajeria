document.addEventListener('DOMContentLoaded', () => {
    // 1. ESCUCHAR EL FORMULARIO DE ALTA MANUAL (EL DE LA IZQUIERDA)
    const formExtraManual = document.getElementById('formExtraManual');
    if (formExtraManual) {
        formExtraManual.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../php/guardar_extra_manual.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    formExtraManual.reset();
                    actualizarTablaExtras();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // 2. ESCUCHAR EL FORMULARIO DEL MODAL (EDICIÓN)
    const formEditarExtra = document.getElementById('formEditarExtra');
    if (formEditarExtra) {
        formEditarExtra.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../php/actualizar_extra.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    cerrarModal('modalEditarExtra');
                    actualizarTablaExtras();
                }
            })
            .catch(error => console.error('Error en edición:', error));
        });
    }
});

// --- FUNCIONES GLOBALES ---

// Refrescar la tabla sin recargar la página
function actualizarTablaExtras() {
    fetch('../php/obtener_extras.php')
    .then(res => res.text())
    .then(html => {
        const cuerpo = document.getElementById('cuerpoTablaExtras');
        if (cuerpo) {
            cuerpo.innerHTML = html;
            
            // BUSCAMOS EL TOTAL EN EL INPUT OCULTO QUE ACABAMOS DE CREAR
            const nuevoTotal = document.getElementById('nuevoTotalB');
            if (nuevoTotal) {
                // Actualizamos el número en el cuadro naranja
                document.getElementById('totalExtras').innerText = parseFloat(nuevoTotal.value).toLocaleString('es-ES', {minimumFractionDigits: 2}) + "€";
            }
        }
    })
    .catch(error => console.error('Error al actualizar tabla:', error));
}

// Abrir el modal de edición y cargar datos
function abrirEditarExtra(extra) {
    // Asegúrate de que estos IDs coincidan con los de tu modal en ingresosb.php
    document.getElementById('edit_extra_id').value = extra.id;
    document.getElementById('edit_extra_concepto').value = extra.concepto;
    document.getElementById('edit_extra_monto').value = extra.monto;
    
    // Mostrar el modal centrado
    document.getElementById('modalEditarExtra').style.display = 'flex';
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}

// Opcional: Función para eliminar si decides añadir el botón de borrar
function eliminarExtra(id) {
    // Confirmación de seguridad
    if (confirm("¿Estás seguro de que quieres eliminar este ingreso extra? Esta acción no se puede deshacer.")) {
        const datos = new FormData();
        datos.append('id', id);

        fetch('../php/eliminar_extra.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.text())
        .then(mensaje => {
            alert(mensaje);
            if (mensaje.includes("✅")) {
                actualizarTablaExtras(); // Esto refrescará la tabla y el total naranja
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('No se pudo eliminar el registro.');
        });
    }
}