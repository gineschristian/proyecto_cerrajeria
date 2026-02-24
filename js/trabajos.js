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
 * Filtra las filas existentes y actualiza el visor dinámico (Dinero + Cantidad)
 */
function filtrarLocalidad() {
    const input = document.getElementById('busquedaLocalidad').value.toLowerCase();
    const filas = document.getElementById('cuerpoTablaTrabajos').getElementsByTagName('tr');
    const visorInfo = document.getElementById('info-filtro');
    const resumenFiltro = document.getElementById('resumenFiltro');
    const totalFacturadoPrincipal = document.getElementById('totalFacturado'); // El total de la parte inferior/superior
    
    let sumaVisible = 0;
    let contador = 0;

    for (let i = 0; i < filas.length; i++) {
        // Ignorar filas de "No hay registros"
        if (filas[i].cells.length < 4) continue;

        const celdaInfo = filas[i].querySelector('.col-info'); 
        const celdaPrecio = filas[i].querySelector('.col-total'); // Cambiado de .precio-fila a .col-total

        if (celdaInfo) {
            const texto = celdaInfo.textContent || celdaInfo.innerText;
            if (texto.toLowerCase().indexOf(input) > -1) {
                filas[i].style.display = "";
                contador++;
                
                // Sumar al total si la fila es visible
                if (celdaPrecio) {
                    // Limpiamos el formato: quitamos el símbolo €, quitamos puntos de mil y cambiamos coma por punto
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

    // Formatear la suma para mostrarla como moneda europea
    const sumaFormateada = sumaVisible.toLocaleString('es-ES', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    }) + '€';

    // 1. Actualizar el visor dinámico (el cuadro azul de búsqueda)
    if (input.length > 0) {
        if (visorInfo) visorInfo.style.display = "block";
        if (resumenFiltro) {
            resumenFiltro.innerHTML = `<b>${contador}</b> encontrados: <span style="color:#27ae60; font-size:1.1rem; font-weight:800;">${sumaFormateada}</span>`;
        }
    } else {
        if (visorInfo) visorInfo.style.display = "none";
    }

    // 2. Sincronizar con el total principal de la página para que no haya confusión
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
            'edit_trabajo_description': trabajo.descripcion, // Corregido de edit_trabajo_desc a edit_trabajo_description según tu HTML
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

function filtrarTrabajos() {
    const inicio = document.getElementById('fechaInicio')?.value || '';
    const fin = document.getElementById('fechaFin')?.value || '';
    const tabla = document.getElementById('cuerpoTablaTrabajos');
    
    if (tabla) { tabla.style.opacity = '0.5'; }

    fetch(`../php/obtener_trabajos.php?inicio=${inicio}&fin=${fin}`)
    .then(res => res.text())
    .then(html => {
        if (tabla) { 
            // El script dentro de 'html' actualizará el totalFacturado inicial
            tabla.innerHTML = html; 
            tabla.style.opacity = '1';

            // Ejecutar cualquier <script> que venga en el HTML (como el que actualiza el total)
            const scripts = tabla.getElementsByTagName('script');
            for (let n = 0; n < scripts.length; n++) {
                eval(scripts[n].innerHTML);
            }

            // Si el buscador de localidad tiene texto, reaplicamos el filtro y la suma
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

function limpiarTodosLosFiltros() {
    const inputLoc = document.getElementById('busquedaLocalidad');
    const inputIni = document.getElementById('fechaInicio');
    const inputFin = document.getElementById('fechaFin');
    const visorInfo = document.getElementById('info-filtro');

    if (inputLoc) inputLoc.value = '';
    if (inputIni) inputIni.value = '';
    if (inputFin) inputFin.value = '';
    if (visorInfo) visorInfo.style.display = 'none';

    filtrarTrabajos(); 
}