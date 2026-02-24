document.addEventListener('DOMContentLoaded', () => {
    const formProducto = document.getElementById('formProducto');
    const formEditarStock = document.getElementById('formEditarStock');
    const buscador = document.getElementById('buscadorStock');

    // --- 1. LÓGICA DEL PANEL DE GUÍA LATERAL ---
    const btnAyuda = document.getElementById('btnAyuda');
    const btnCerrarGuia = document.getElementById('btnCerrarGuia');
    const panelGuia = document.getElementById('panelGuia');

    if (btnAyuda && panelGuia) {
        btnAyuda.addEventListener('click', () => panelGuia.classList.add('active'));
    }
    if (btnCerrarGuia && panelGuia) {
        btnCerrarGuia.addEventListener('click', () => panelGuia.classList.remove('active'));
    }

    // --- 2. REGISTRO DE NUEVO PRODUCTO ---
    if (formProducto) {
        formProducto.addEventListener('submit', function(e) {
            e.preventDefault();
            let datos = new FormData(this); 
            fetch('../php/guardar_producto.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje); 
                formProducto.reset();
                actualizarTabla(); 
            })
            .catch(error => console.error('Error al guardar:', error));
        });
    }

    // --- 3. ACTUALIZACIÓN DE PRODUCTO EXISTENTE (DINÁMICO) ---
    if (formEditarStock) {
        formEditarStock.addEventListener('submit', function(e) {
            e.preventDefault();
            let datos = new FormData(this);

            fetch('../php/actualizar_stock.php', { method: 'POST', body: datos })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje);
                if (mensaje.includes("✅")) {
                    cerrarModal('modalEditarStock');
                    actualizarTabla(); 
                }
            })
            .catch(error => console.error('Error al actualizar:', error));
        });
    }

    // --- 4. BUSCADOR EN TIEMPO REAL ---
    if (buscador) {
        buscador.addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let filas = document.querySelectorAll('#cuerpoTabla tr');
            filas.forEach(fila => {
                let nombre = fila.cells[1] ? fila.cells[1].textContent.toLowerCase() : "";
                let categoria = fila.cells[2] ? fila.cells[2].textContent.toLowerCase() : "";
                fila.style.display = (nombre.includes(filtro) || categoria.includes(filtro)) ? "" : "none";
            });
        });
    }
});

// --- 5. FUNCIÓN DE FILTRADO POR UBICACIÓN ESCALABLE ---
function filtrarUbicacion(idUsuario) {
    const filas = document.querySelectorAll('#cuerpoTabla tr');
    const buscador = document.getElementById('buscadorStock');
    if(buscador) buscador.value = "";

    filas.forEach(fila => {
        if (idUsuario === 'todos') {
            fila.style.display = "";
            return;
        }

        // Buscamos la celda que tiene el data-user-id correspondiente
        const celdasStock = fila.querySelectorAll('td[data-user-id]');
        let valor = 0;

        if (idUsuario === 'taller') {
            valor = parseInt(fila.cells[3].textContent) || 0;
        } else {
            celdasStock.forEach(celda => {
                if (celda.getAttribute('data-user-id') == idUsuario) {
                    valor = parseInt(celda.textContent) || 0;
                }
            });
        }
        fila.style.display = (valor > 0) ? "" : "none";
    });
}

// --- FUNCIONES GLOBALES ---

function actualizarTabla() {
    fetch('../php/obtener_producto.php?t=' + Date.now())
        .then(res => res.text())
        .then(html => {
            const cuerpo = document.getElementById('cuerpoTabla');
            if (cuerpo) cuerpo.innerHTML = html;
        })
        .catch(error => console.error('Error al refrescar tabla:', error));
}

// ABRIR MODAL CON CARGA DINÁMICA DE EMPLEADOS
function abrirEditarStock(producto) {
    document.getElementById('edit_id').value = producto.id;
    document.getElementById('edit_nombre').value = producto.nombre;
    document.getElementById('edit_categoria').value = producto.categoria;
    
    // Contenedor donde generaremos los inputs
    const contenedor = document.getElementById('contenedorCantidadesDinamicas');
    if (!contenedor) return;
    
    contenedor.innerHTML = ''; // Limpiar

    // 1. Input para Taller (Almacén Central)
    contenedor.innerHTML += `
        <div class="input-group">
            <label>🏭 Taller (Stock Central)</label>
            <input type="number" name="cant_almacen" value="${producto.cant_almacen}" required>
        </div>
    `;

    // 2. Generar Inputs para cada Empleado/Furgoneta detectada en la tabla
    const cabeceras = document.querySelectorAll('#tablaStock th[data-user-id]');
    
    cabeceras.forEach(th => {
        const idUsuario = th.getAttribute('data-user-id');
        const nombreUsuario = th.textContent.replace('F. ', '');
        // El stock viene del desglose que enviamos en el JSON del PHP
        const cantidadActual = (producto.desglose_stock && producto.desglose_stock[idUsuario]) 
                               ? producto.desglose_stock[idUsuario] : 0;

        contenedor.innerHTML += `
            <div class="input-group">
                <label>🚐 F. ${nombreUsuario}</label>
                <input type="number" name="stock_usuarios[${idUsuario}]" value="${cantidadActual}" required>
            </div>
        `;
    });

    document.getElementById('modalEditarStock').style.display = 'flex';
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}

function eliminar(id) {
    if (confirm("¿Estás seguro de que quieres eliminar este material?")) {
        const datos = new FormData();
        datos.append('id', id);
        fetch('../php/eliminar_producto.php', { method: 'POST', body: datos })
        .then(res => res.text())
        .then(mensaje => {
            alert(mensaje);
            actualizarTabla(); 
        })
        .catch(error => console.error('Error al eliminar:', error));
    }
}