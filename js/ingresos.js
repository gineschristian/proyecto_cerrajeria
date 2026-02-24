document.addEventListener('DOMContentLoaded', () => {
    console.log("JS Cargado correctamente"); // Verás esto en la consola al refrescar
    
    const formEditar = document.getElementById('formEditarIngreso');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            const datos = new FormData(this);
            fetch('../php/actualizar_ingreso.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) { cerrarModal('modalEditarIngreso'); filtrarIngresos(); }
            });
        });
    }
    // Calcular al cargar
    setTimeout(actualizarSumaIngresosFiltrados, 800);
});

function filtrarIngresos() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;

    console.log("Filtrando desde:", inicio, "hasta:", fin);

    fetch(`../php/obtener_ingresos_totales.php?inicio=${inicio}&fin=${fin}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('cuerpoTablaIngresos').innerHTML = html;
            // Damos tiempo a la tabla para que aparezca
            setTimeout(actualizarSumaIngresosFiltrados, 300);
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

            // Limpieza inteligente de moneda europea
            // Si tiene punto y coma (1.234,56), quitamos el punto y cambiamos coma por punto
            if (textoMonto.includes(',') && textoMonto.includes('.')) {
                textoMonto = textoMonto.replace(/\./g, '');
                textoMonto = textoMonto.replace(',', '.');
            } 
            // Si solo tiene coma (1234,56), la cambiamos por punto
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

    // ESTO ES LO QUE ACTUALIZA LOS CUADROS
    // Asegúrate de que estos IDs existan en tu ingresos.php
    const domA = document.getElementById('totalOficial');
    const domB = document.getElementById('totalB');
    const domGen = document.getElementById('totalGeneral');

    if (domA) domA.innerText = sumaA.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
    if (domB) domB.innerText = sumaB.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
    if (domGen) domGen.innerText = (sumaA + sumaB).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
}