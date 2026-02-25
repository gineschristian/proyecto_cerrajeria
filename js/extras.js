document.addEventListener('DOMContentLoaded', () => {
    const formExtraManual = document.getElementById('formExtraManual');
    const formEditarExtra = document.getElementById('formEditarExtra');

    // 1. Manejar envío de nuevo ingreso
    if (formExtraManual) {
        formExtraManual.addEventListener('submit', function(e) {
            e.preventDefault();
            const datos = new FormData(this);
            fetch('../php/guardar_extra.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    this.reset();
                    filtrarExtras(); 
                }
            });
        });
    }

    // 2. Manejar edición
    if (formEditarExtra) {
        formEditarExtra.addEventListener('submit', function(e) {
            e.preventDefault();
            const datos = new FormData(this);
            fetch('../php/actualizar_extra.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    cerrarModal('modalEditarExtra');
                    filtrarExtras();
                }
            });
        });
    }

    // ELIMINADO EL SETTIMEOUT: Ya no hace falta porque el PHP carga el total al inicio.
});

// 3. FUNCIÓN DE FILTRADO
function filtrarExtras() {
    const inicio = document.getElementById('fechaInicioB').value;
    const fin = document.getElementById('fechaFinB').value;

    fetch(`../php/obtener_extras.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('cuerpoTablaExtras').innerHTML = html;
            // Solo recalculamos cuando el usuario interactúa con los filtros
            actualizarTotalExtras();
        })
        .catch(error => console.error('Error al filtrar:', error));
}

function limpiarFiltroExtras() {
    document.getElementById('fechaInicioB').value = "";
    document.getElementById('fechaFinB').value = "";
    filtrarExtras();
}

// 4. ACTUALIZAR TOTAL
function actualizarTotalExtras() {
    const inputTotal = document.getElementById('nuevoTotalB');
    const domTotal = document.getElementById('totalExtras');

    // Si el PHP ha inyectado un nuevo input de total tras el fetch
    if (inputTotal && domTotal) {
        let valorCifrado = parseFloat(inputTotal.value);
        domTotal.innerText = valorCifrado.toLocaleString('de-DE', {minimumFractionDigits: 2}) + '€';
    } else {
        // Cálculo manual de respaldo si el input no existe
        let totalManual = 0;
        const filas = document.querySelectorAll('#cuerpoTablaExtras tr');

        filas.forEach(fila => {
            if (fila.cells.length >= 3) {
                let textoMonto = fila.cells[2].innerText.toUpperCase()
                    .replace('MONTO', '').replace('€', '').replace(/[^\d,.-]/g, '').trim();

                if (textoMonto.includes(',') && textoMonto.includes('.')) {
                    textoMonto = textoMonto.replace(/\./g, '');
                }
                textoMonto = textoMonto.replace(',', '.');

                let valor = parseFloat(textoMonto);
                if (!isNaN(valor)) totalManual += valor;
            }
        });
        if (domTotal) domTotal.innerText = totalManual.toLocaleString('de-DE', {minimumFractionDigits: 2}) + '€';
    }
}

// 5. FUNCIÓN ELIMINAR
function eliminarExtra(id) {
    if (confirm('¿Seguro que deseas eliminar este ingreso extra?')) {
        fetch(`../php/eliminar_extra.php?id=${id}`)
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                filtrarExtras();
            })
            .catch(error => console.error('Error:', error));
    }
}

// 6. MODALES
function abrirEditarExtra(extra) {
    document.getElementById('edit_extra_id').value = extra.id;
    document.getElementById('edit_extra_concepto').value = extra.concepto;
    document.getElementById('edit_extra_monto').value = extra.monto;
    document.getElementById('modalEditarExtra').style.display = 'flex';
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}