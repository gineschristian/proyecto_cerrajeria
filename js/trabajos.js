/**
 * TRABAJOS.JS 
 * Gestión de registro, edición, eliminación y filtrado con visor de totales.
 */

document.addEventListener('DOMContentLoaded', () => {
    const formTrabajo = document.getElementById('formTrabajo');
    const formEditarTrabajo = document.getElementById('formEditarTrabajo');

    // 1. REGISTRAR NUEVO TRABAJO
    if (formTrabajo) {
        formTrabajo.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnGuardar = this.querySelector('.btn-guardar');
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = "⌛ Guardando...";

            let datos = new FormData(this);

            fetch('../php/guardar_trabajo.php', {
                method: 'POST',
                body: datos
            })
            .then(res => res.text())
            .then(mensaje => {
                const respuesta = mensaje.trim();

                if (respuesta === 'success' || respuesta.includes('✅')) {
                    alert('✅ ¡Trabajo guardado con éxito!');
                    formTrabajo.reset();
                    
                    const contenedor = document.getElementById('contenedor-materiales');
                    if (contenedor) { contenedor.innerHTML = ''; }
                    
                    filtrarTrabajos(); 
                } else {
                    alert(respuesta); 
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión con el servidor.');
            })
            .finally(() => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
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
                    alert("⚠️ Error: " + mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión.');
            });
        });
    }
});

// --- FUNCIONES GLOBALES ---

/**
 * Filtro en tiempo real por Localidad
 */
function filtrarLocalidad() {
    const input = document.getElementById('busquedaLocalidad').value.toLowerCase();
    const filas = document.getElementById('cuerpoTablaTrabajos').getElementsByTagName('tr');
    const visorInfo = document.getElementById('info-filtro');
    const resumenFiltro = document.getElementById('resumenFiltro');
    const totalFacturadoPrincipal = document.getElementById('totalFacturado'); 
    
    let sumaVisible = 0;
    let contador = 0;

    for (let i = 0; i < filas.length; i++) {
        if (filas[i].cells.length < 4) continue;

        const celdaInfo = filas[i].querySelector('.col-info'); 
        const celdaPrecio = filas[i].querySelector('.col-total');

        if (celdaInfo) {
            const texto = celdaInfo.textContent || celdaInfo.innerText;
            if (texto.toLowerCase().indexOf(input) > -1) {
                filas[i].style.display = "";
                contador++;
                
                if (celdaPrecio) {
                    let precioTexto = celdaPrecio.innerText.replace('€', '').trim();
                    let precioNum = parseFloat(precioTexto.replace(/\./g, '').replace(',', '.'));
                    
                    if (!isNaN(precioNum)) {
                        sumaVisible += precioNum;
                    }
                }
            } else {
                filas[i].style.display = "none";
            }
        }
    }

    const sumaFormateada = sumaVisible.toLocaleString('es-ES', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }) + '€';

    if (input.length > 0) {
        if (visorInfo) visorInfo.style.display = "block";
        if (resumenFiltro) {
            resumenFiltro.innerHTML = `<b>${contador}</b> encontrados: <span style="color:#27ae60; font-size:1.1rem; font-weight:800;">${sumaFormateada}</span>`;
        }
    } else {
        if (visorInfo) visorInfo.style.display = "none";
    }

    if (totalFacturadoPrincipal) {
        totalFacturadoPrincipal.innerText = sumaFormateada;
    }
}

function abrirEditarTrabajo(trabajo) {
    try {
        const campos = {
            'edit_trabajo_id': trabajo.id,
            'edit_trabajo_cliente': trabajo.cliente,
            'edit_trabajo_nombre_cliente': trabajo.nombre_cliente,
            'edit_trabajo_telefono': trabajo.telefono,
            'edit_trabajo_localidad': trabajo.localidad,
            'edit_trabajo_description': trabajo.descripcion,
            'edit_trabajo_precio': trabajo.precio_total
        };

        for (let id in campos) {
            const el = document.getElementById(id);
            if (el) { el.value = campos[id] || ''; }
        }

        const modal = document.getElementById('modalEditarTrabajo');
        if (modal) { modal.style.display = 'flex'; }
    } catch (e) {
        console.error("Error al abrir modal:", e);
    }
}

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
            alert("🗑️ Registro eliminado y stock devuelto.");
            filtrarTrabajos();
        } else {
            alert(mensaje);
        }
    })
    .catch(error => console.error('Error:', error));
}

// --- FUNCIÓN MODIFICADA PARA FILTRAR POR EMPLEADO ---
function filtrarTrabajos() {
    const inicio = document.getElementById('fechaInicio')?.value || '';
    const fin = document.getElementById('fechaFin')?.value || '';
    // Capturamos el nuevo filtro de empleado
    const empleado = document.getElementById('filtroEmpleado')?.value || '';
    
    const tabla = document.getElementById('cuerpoTablaTrabajos');
    
    if (tabla) { tabla.style.opacity = '0.5'; }

    // Añadimos el parámetro 'empleado' a la URL
    fetch(`../php/obtener_trabajos.php?inicio=${inicio}&fin=${fin}&empleado=${encodeURIComponent(empleado)}`)
    .then(res => res.text())
    .then(html => {
        if (tabla) { 
            tabla.innerHTML = html; 
            tabla.style.opacity = '1';

            const scripts = tabla.getElementsByTagName('script');
            for (let n = 0; n < scripts.length; n++) {
                eval(scripts[n].innerHTML);
            }

            if (document.getElementById('busquedaLocalidad')?.value !== "") {
                filtrarLocalidad();
            }
        }
    })
    .catch(error => {
        console.error('Error al filtrar:', error);
        if (tabla) { tabla.style.opacity = '1'; }
    });
}

function cerrarModal(id) {
    const modal = document.getElementById(id);
    if (modal) { modal.style.display = 'none'; }
}

// --- FUNCIÓN ACTUALIZADA ---
function limpiarTodosLosFiltros() {
    const inputLoc = document.getElementById('busquedaLocalidad');
    const inputIni = document.getElementById('fechaInicio');
    const inputFin = document.getElementById('fechaFin');
    const inputEmp = document.getElementById('filtroEmpleado'); // Añadido selector empleado
    const visorInfo = document.getElementById('info-filtro');

    if (inputLoc) inputLoc.value = '';
    if (inputIni) inputIni.value = '';
    if (inputFin) inputFin.value = '';
    if (inputEmp) inputEmp.value = ''; // Limpiamos el selector
    if (visorInfo) visorInfo.style.display = 'none';

    filtrarTrabajos(); 
}

// Nota: Asegúrate de que el botón de "Reset" en tu HTML llame a limpiarTodosLosFiltros()
// o renombra la función en el HTML a limpiarFiltros() si así lo prefieres.