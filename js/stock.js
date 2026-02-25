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

    // --- 3. ACTUALIZACIÓN DE PRODUCTO EXISTENTE ---
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

// --- 5. FILTRADO POR UBICACIÓN ---
function filtrarUbicacion(idUsuario) {
    const filas = document.querySelectorAll('#cuerpoTabla tr');
    const buscador = document.getElementById('buscadorStock');
    if(buscador) buscador.value = "";

    filas.forEach(fila => {
        if (idUsuario === 'todos') {
            fila.style.display = "";
            return;
        }

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

// --- 6. CATEGORÍAS (AÑADIR Y ELIMINAR) ---

function guardarNuevaCategoria() {
    const nombreInput = document.getElementById('nuevaCategoriaNombre');
    const nombre = nombreInput.value.trim();
    
    if (!nombre) {
        alert("Escribe un nombre para la categoría");
        return;
    }

    const datos = new URLSearchParams();
    datos.append('nombre', nombre);

    fetch('../php/guardar_categoria.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: datos
    })
    .then(res => res.text())
    .then(mensaje => {
        if (mensaje.includes("✅")) {
            location.reload(); 
        } else {
            alert(mensaje);
        }
    })
    .catch(error => console.error('Error:', error));
}

function eliminarCategoria(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar la categoría "${nombre}"?\n\nNota: Los productos no se borrarán, pero se quedarán sin categoría.`)) {
        
        const datos = new URLSearchParams();
        datos.append('id', id);

        fetch('../php/eliminar_categoria.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos
        })
        .then(res => res.text())
        .then(mensaje => {
            if (mensaje.includes("✅")) {
                location.reload();
            } else {
                alert("Error: " + mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// --- 7. FUNCIONES GLOBALES DE TABLA Y MODAL ---

function actualizarTabla() {
    fetch('../php/obtener_producto.php?t=' + Date.now())
        .then(res => res.text())
        .then(html => {
            const cuerpo = document.getElementById('cuerpoTabla');
            if (cuerpo) cuerpo.innerHTML = html;
        })
        .catch(error => console.error('Error al refrescar tabla:', error));
}

function abrirEditarStock(producto) {
    document.getElementById('edit_id').value = producto.id;
    document.getElementById('edit_nombre').value = producto.nombre;
    document.getElementById('edit_categoria').value = producto.categoria;
    
    const contenedor = document.getElementById('contenedorCantidadesDinamicas');
    if (!contenedor) return;
    
    contenedor.innerHTML = ''; 

    // Taller
    contenedor.innerHTML += `
        <div class="input-group">
            <label>🏭 Taller (Stock Central)</label>
            <input type="number" name="cant_almacen" value="${producto.cant_almacen}" required>
        </div>
    `;

    // Empleados
    const cabeceras = document.querySelectorAll('#tablaStock th[data-user-id]');
    cabeceras.forEach(th => {
        const idUsuario = th.getAttribute('data-user-id');
        const nombreUsuario = th.textContent.replace('F. ', '');
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
function editarCategoria(id, nombreAntiguo) {
    const nuevoNombre = prompt("Editar nombre de la categoría:", nombreAntiguo);
    
    if (nuevoNombre !== null && nuevoNombre.trim() !== "" && nuevoNombre !== nombreAntiguo) {
        const datos = new URLSearchParams();
        datos.append('id', id);
        datos.append('nombre', nuevoNombre.trim());

        fetch('../php/editar_categoria.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos
        })
        .then(res => res.text())
        .then(mensaje => {
            if (mensaje.includes("✅")) {
                location.reload();
            } else {
                alert(mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}