document.addEventListener('DOMContentLoaded', () => {
    const formEditarIngreso = document.getElementById('formEditarIngreso');

    // 1. MANEJAR EL ENVÍO DEL FORMULARIO DE EDICIÓN
    if (formEditarIngreso) {
        formEditarIngreso.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const datos = new FormData(this);

            fetch('../php/actualizar_ingreso.php', {
                method: 'POST',
                body: datos
            })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    cerrarModal('modalEditarIngreso');
                    // Recargamos la tabla para ver los cambios y los totales actualizados
                    filtrarIngresos(); 
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('No se pudo actualizar el ingreso.');
            });
        });
    }
});

// --- FUNCIONES GLOBALES ---

// 2. FUNCIÓN PARA ABRIR EL MODAL Y RELLENAR DATOS
function abrirEditarIngreso(ingreso) {
    // Rellenamos los campos del modal con los datos que vienen de la base de datos
    document.getElementById('edit_ingreso_id').value = ingreso.id;
    document.getElementById('edit_ingreso_concepto').value = ingreso.concepto;
    document.getElementById('edit_ingreso_monto').value = ingreso.monto;
    
    // Si usas una sola tabla para A y B, aquí podrías identificar de cuál viene
    if(document.getElementById('edit_ingreso_tabla')) {
        document.getElementById('edit_ingreso_tabla').value = ingreso.tipo || ''; 
    }

    // Mostramos la pantalla emergente
    document.getElementById('modalEditarIngreso').style.display = 'flex';
}

// 3. FUNCIÓN PARA CERRAR EL MODAL
function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}

// 4. FUNCIÓN PARA FILTRAR POR FECHAS (AJAX)
function filtrarIngresos() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;

    // Llamamos al PHP pasándole las fechas por la URL
    fetch(`../php/obtener_ingresos_totales.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('cuerpoTablaIngresos').innerHTML = html;
        })
        .catch(error => console.error('Error al filtrar:', error));
}