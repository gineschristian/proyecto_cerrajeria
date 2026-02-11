document.addEventListener('DOMContentLoaded', () => {
    const formTrabajo = document.getElementById('formTrabajo');
    const formEditarTrabajo = document.getElementById('formEditarTrabajo');

    // 1. REGISTRAR NUEVO TRABAJO
    if (formTrabajo) {
        formTrabajo.addEventListener('submit', function(e) {
            e.preventDefault();
            let datos = new FormData(this);

            fetch('../php/guardar_trabajo.php', {
                method: 'POST',
                body: datos
            })
            .then(res => res.text())
            .then(mensaje => {
                // Verificamos si la respuesta indica éxito (ahora nuestro PHP devuelve 'success')
                if (mensaje.trim() === 'success' || mensaje.includes('✅')) {
                    alert('✅ ¡Trabajo guardado con éxito!');
                    
                    // Limpiamos el formulario
                    formTrabajo.reset();
                    
                    // IMPORTANTE: Limpiar las filas de productos añadidas dinámicamente
                    const contenedor = document.getElementById('contenedor-materiales');
                    if (contenedor) {
                        contenedor.innerHTML = ''; 
                    }
                    
                    // Actualizamos la tabla sin recargar toda la página
                    filtrarTrabajos();
                } else {
                    alert("⚠️ Error al guardar: " + mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor.');
            });
        });
    }

    // 2. ACTUALIZAR TRABAJO (MODAL)
    if (formEditarTrabajo) {
        formEditarTrabajo.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../php/actualizar_trabajo.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(mensaje => {
                if (mensaje.trim() === "success" || mensaje.includes("✅")) {
                    alert("✅ ¡Trabajo actualizado correctamente!");
                    cerrarModal('modalEditarTrabajo');
                    filtrarTrabajos();
                } else {
                    alert("⚠️ Error al actualizar: " + mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al actualizar.');
            });
        });
    }
});

// --- FUNCIONES GLOBALES ---

// 3. ABRIR MODAL DE EDICIÓN
function abrirEditarTrabajo(trabajo) {
    try {
        const campos = {
            'edit_trabajo_id': trabajo.id,
            'edit_trabajo_cliente': trabajo.cliente,
            'edit_trabajo_desc': trabajo.descripcion,
            'edit_trabajo_precio': trabajo.precio_total,
            'edit_trabajo_estado': trabajo.estado || 'Pendiente'
        };

        for (let id in campos) {
            const el = document.getElementById(id);
            if (el) el.value = campos[id] || '';
        }

        const modal = document.getElementById('modalEditarTrabajo');
        if (modal) modal.style.display = 'flex';

    } catch (e) {
        console.error("Error al abrir modal:", e);
    }
}

// 4. ELIMINAR TRABAJO
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
        if (mensaje.trim() === 'success' || mensaje.includes('✅')) {
            alert("🗑️ Registro eliminado.");
            filtrarTrabajos();
        } else {
            alert(mensaje);
        }
    })
    .catch(error => console.error('Error:', error));
}

// 5. FILTRAR POR FECHAS (Y RECARGA DINÁMICA)
function filtrarTrabajos() {
    // Obtenemos las fechas si existen, si no enviamos vacío
    const inicio = document.getElementById('fechaInicio')?.value || '';
    const fin = document.getElementById('fechaFin')?.value || '';

    fetch(`../php/obtener_trabajos.php?inicio=${inicio}&fin=${fin}`)
    .then(res => res.text())
    .then(html => {
        const tabla = document.getElementById('cuerpoTablaTrabajos');
        if (tabla) {
            tabla.innerHTML = html;
        }
    })
    .catch(error => console.error('Error al filtrar:', error));
}

// 6. CERRAR MODALES
function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}