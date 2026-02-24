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
            document.getElementById('formGasto').reset();
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
        // Recalculamos el total automáticamente al actualizar la tabla
        actualizarSumaTotalFiltrada();
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
    if(document.getElementById('edit_proveedor')) {
        document.getElementById('edit_proveedor').value = gasto.proveedor;
    }
    document.getElementById('edit_concepto').value = gasto.concepto;
    document.getElementById('edit_monto').value = gasto.monto;
    document.getElementById('edit_categoria').value = gasto.categoria;
    document.getElementById('edit_factura').value = gasto.con_factura;
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
        actualizarTablaGastos();
    });
});

// --- NUEVAS FUNCIONES PARA EL FILTRO DE CALENDARIO ---

function filtrarGastosFecha() {
    const inicio = document.getElementById('fechaInicio').value;
    const fin = document.getElementById('fechaFin').value;
    const filas = document.querySelectorAll('#cuerpoTablaGastos tr');
    
    if (!inicio || !fin) {
        alert("Por favor selecciona ambas fechas (Desde y Hasta)");
        return;
    }

    const dInicio = new Date(inicio);
    const dFin = new Date(fin);
    dFin.setHours(23, 59, 59); // Para incluir todo el día final

    filas.forEach(fila => {
        // Obtenemos la fecha de la columna 0. Asumimos formato DD/MM/YYYY
        const fechaTexto = fila.cells[0].innerText.trim(); 
        const partes = fechaTexto.split('/');
        
        if(partes.length === 3) {
            const fechaFila = new Date(partes[2], partes[1] - 1, partes[0]);

            if (fechaFila >= dInicio && fechaFila <= dFin) {
                fila.style.display = "";
            } else {
                fila.style.display = "none";
            }
        }
    });

    actualizarSumaTotalFiltrada();
}

function limpiarFiltroFecha() {
    document.getElementById('fechaInicio').value = "";
    document.getElementById('fechaFin').value = "";
    const filas = document.querySelectorAll('#cuerpoTablaGastos tr');
    filas.forEach(fila => fila.style.display = "");
    
    actualizarSumaTotalFiltrada();
}

function actualizarSumaTotalFiltrada() {
    let total = 0;
    const filas = document.querySelectorAll('#cuerpoTablaGastos tr');
    
    filas.forEach(fila => {
        // Solo sumamos si la fila es visible (no filtrada)
        if (fila.style.display !== "none") {
            // El monto está en la columna 5 (Monto)
            // Quitamos el símbolo € y limpiamos espacios para poder sumar
            let montoTexto = fila.cells[5].innerText.replace('€', '').trim();
            // Reemplazamos coma por punto por si viene en formato 10,50
            montoTexto = montoTexto.replace(',', '.');
            let valor = parseFloat(montoTexto);
            if (!isNaN(valor)) {
                total += valor;
            }
        }
    });
    
    const elementoTotal = document.getElementById('totalGastos');
    if (elementoTotal) {
        elementoTotal.innerText = total.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '€';
    }
}

// Ejecutar suma inicial al cargar la página
window.onload = function() {
    actualizarSumaTotalFiltrada();
};