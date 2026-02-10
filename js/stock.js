document.addEventListener('DOMContentLoaded', () => {
    const formProducto = document.getElementById('formProducto');
    const formEditarStock = document.getElementById('formEditarStock');
    const buscador = document.getElementById('buscadorStock');

    // --- 1. LÓGICA DEL PANEL DE GUÍA LATERAL ---
    const btnAyuda = document.getElementById('btnAyuda');
    const btnCerrarGuia = document.getElementById('btnCerrarGuia');
    const panelGuia = document.getElementById('panelGuia');

    if (btnAyuda && panelGuia) {
        btnAyuda.addEventListener('click', () => {
            panelGuia.classList.add('active');
        });
    }

    if (btnCerrarGuia && panelGuia) {
        btnCerrarGuia.addEventListener('click', () => {
            panelGuia.classList.remove('active');
        });
    }

    // --- 2. REGISTRO DE NUEVO PRODUCTO ---
    if (formProducto) {
        formProducto.addEventListener('submit', function(e) {
            e.preventDefault();
            let datos = new FormData(this); 

            fetch('../php/guardar_producto.php', {
                method: 'POST',
                body: datos
            })
            .then(res => res.text())
            .then(mensaje => {
                alert(mensaje); 
                formProducto.reset();
                actualizarTabla(); 
            })
            .catch(error => console.error('Error al guardar:', error));
        });
    }

    // --- 3. ACTUALIZACIÓN DE PRODUCTO EXISTENTE (MODAL) ---
    if (formEditarStock) {
        formEditarStock.addEventListener('submit', function(e) {
            e.preventDefault();
            let datos = new FormData(this);

            fetch('../php/actualizar_stock.php', {
                method: 'POST',
                body: datos
            })
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
                let nombre = fila.cells[1].textContent.toLowerCase();
                let categoria = fila.cells[2].textContent.toLowerCase();
                fila.style.display = (nombre.includes(filtro) || categoria.includes(filtro)) ? "" : "none";
            });
        });
    }
});

// --- FUNCIONES GLOBALES (Fuera del DOMContentLoaded para que sean accesibles desde el HTML) ---

// Refrescar la tabla mediante AJAX
function actualizarTabla() {
    fetch('../php/obtener_producto.php')
        .then(res => res.text())
        .then(html => {
            const cuerpo = document.getElementById('cuerpoTabla');
            if (cuerpo) cuerpo.innerHTML = html;
        })
        .catch(error => console.error('Error al refrescar tabla:', error));
}

// Abrir Modal y rellenar datos
function abrirEditarStock(producto) {
    document.getElementById('edit_id').value = producto.id;
    document.getElementById('edit_nombre').value = producto.nombre;
    document.getElementById('edit_cantidad').value = producto.cantidad;
    document.getElementById('edit_categoria').value = producto.categoria;
    
    document.getElementById('modalEditarStock').style.display = 'flex';
}

function cerrarModal(idModal) {
    document.getElementById(idModal).style.display = 'none';
}

// Eliminar Producto
function eliminar(id) {
    if (confirm("¿Estás seguro de que quieres eliminar este material?")) {
        const datos = new FormData();
        datos.append('id', id);

        fetch('../php/eliminar_producto.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.text())
        .then(mensaje => {
            alert(mensaje);
            actualizarTabla(); 
        })
        .catch(error => console.error('Error al eliminar:', error));
    }
}