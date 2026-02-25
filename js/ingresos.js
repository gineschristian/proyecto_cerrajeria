document.addEventListener('DOMContentLoaded', () => {
    console.log("JS Cargado correctamente");
    
    const formEditar = document.getElementById('formEditarIngreso');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            const datos = new FormData(this);
            fetch('../php/actualizar_ingreso.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) { 
                    cerrarModal('modalEditarIngreso'); 
                    filtrarIngresos(); 
                }
            });
        });
    }

    // ELIMINADO: setTimeout(actualizarSumaIngresosFiltrados, 800);
    // Ya no hace falta porque PHP carga los totales directamente en el HTML.
});

function filtrarIngresos() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;

    console.log("Filtrando desde:", inicio, "hasta:", fin);

    fetch(`../php/obtener_ingresos_totales.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('cuerpoTablaIngresos').innerHTML = html;
            // Solo recalculamos después de una acción de filtrado manual
            actualizarSumaIngresosFiltrados();
        })
        .catch(error => console.error('Error AJAX:', error));
}

function limpiarFiltroIngresos() {
    document.getElementById('fechaInicio').value = "";
    document.getElementById('fechaFin').value = "";
    filtrarIngresos();
}

function actualizarSumaIngresosFiltrados() {
    let sumaA = 0; 
    let sumaB = 0; 

    const filas = document.querySelectorAll('#cuerpoTablaIngresos tr');
    
    filas.forEach((fila) => {
        if (fila.cells.length >= 4) {
            let tipo = fila.cells[2].innerText.toUpperCase().replace('TIPO', '').trim();
            let textoMonto = fila.cells[3].innerText.replace('MONTO', '').replace('€', '').trim();

            if (textoMonto.includes(',') && textoMonto.includes('.')) {
                textoMonto = textoMonto.replace(/\./g, '');
                textoMonto = textoMonto.replace(',', '.');
            } 
            else if (textoMonto.includes(',')) {
                textoMonto = textoMonto.replace(',', '.');
            }

            let valor = parseFloat(textoMonto);

            if (!isNaN(valor)) {
                if (tipo.includes("OFICIAL") || tipo.includes("A") || tipo.includes("FACTURA")) {
                    sumaA += valor;
                } else {
                    sumaB += valor;
                }
            }
        }
    });

    const domA = document.getElementById('totalOficial');
    const domB = document.getElementById('totalB');
    const domGen = document.getElementById('totalGeneral');

    if (domA) domA.innerText = sumaA.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
    if (domB) domB.innerText = sumaB.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
    if (domGen) domGen.innerText = (sumaA + sumaB).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
}

// FUNCIONES DE MODAL (Mantenidas para compatibilidad)
function cerrarModal(idModal) {
    const modal = document.getElementById(idModal);
    if (modal) modal.style.display = 'none';
}

function eliminarIngreso(id, tabla) {
    if (confirm('¿Seguro que deseas eliminar este ingreso?')) {
        fetch(`../php/eliminar_ingreso.php?id=${id}&tabla=${tabla}`)
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                filtrarIngresos();
            });
    }
}