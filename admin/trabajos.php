<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. DEFINIMOS LA VARIABLE QUE FALTA (Soluciona el error de la imagen)
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// 3. Incluimos la conexión con la ruta correcta (subiendo un nivel)
include '../php/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Trabajos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css?v=1.2">
    <link rel="stylesheet" href="../css/formularios.css?v=1.2">
    <link rel="stylesheet" href="../css/trabajos_layout.css?v=1.2">
    <style>
        header { background-color: #2c3e50 !important; }
        .trabajos-container-dual { display: flex; flex-wrap: wrap; gap: 20px; padding: 15px; }
        .columna-formulario, .columna-tabla { flex: 1; min-width: 320px; }

        .zona-material {
    background: #ebf2f7;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px dashed #3498db;
    width: 100%; /* Evita que se salga del padre */
    box-sizing: border-box;
}

/* Ajuste de cada fila para que no se desborde */
.fila-material {
    display: flex;
    flex-wrap: nowrap; /* Mantiene todo en una línea en PC */
    gap: 8px;
    margin-bottom: 10px;
    align-items: center;
}

/* El selector de producto debe ser el que más espacio ocupe */
.fila-material select {
    flex: 2; 
    min-width: 0; /* Truco de flexbox para que no ignore el ancho del padre */
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

/* El input de cantidad debe ser pequeño pero fijo */
.fila-material input[type="number"] {
    flex: 0 0 60px; /* No crece, no encoge, base de 60px */
    width: 60px;
    padding: 8px;
    text-align: center;
}

/* Ajustes para móviles */
@media (max-width: 480px) {
    .fila-material {
        flex-wrap: wrap; /* En móviles muy pequeños, permite dos líneas si es necesario */
    }
    .fila-material select {
        flex: 1 1 100%; /* El select ocupa toda la línea arriba */
    }
    .fila-material input[type="number"] {
        flex: 1; /* La cantidad y el botón X se reparten la línea de abajo */
    }
}

        @media (max-width: 850px) {
            .trabajos-container-dual { flex-direction: column; }
            .nav-container { display: flex; overflow-x: auto; padding-bottom: 10px; gap: 10px; }
            .btn-header { white-space: nowrap; }
        }

        .radio-group { display: flex; gap: 20px; margin-top: 10px; }
        .radio-label { background: #f8f9fa; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; flex: 1; text-align: center; cursor: pointer; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 25px; border-radius: 12px; width: 95%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Trabajos- Cerrajería Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="plantillas.php"class="btn-header">🗒️ Plantillas</a>
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="empleados.php"class="btn-header">👥 Empleados</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <?php endif; ?>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        <aside class="columna-formulario">
            <div class="card-formulario">
                <h2 style="border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">Nuevo Trabajo</h2>
                <form id="formTrabajo" class="mi-formulario">
                    <div class="input-group">
                        <label>Cliente / Dirección</label>
                        <input type="text" name="cliente" placeholder="Ej: Calle Mayor 5, 2B" required>
                    </div>

                    <div class="input-group">
                        <label>Descripción</label>
                        <textarea name="description" placeholder="Apertura, cambio de bombín..." rows="3"></textarea>
                    </div>

                    <div class="zona-material" style="background: #ebf2f7; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <label><strong>Materiales utilizados</strong></label>
                        <div id="contenedor-materiales">
                            </div>
                        <button type="button" onclick="agregarFilaMaterial()" style="width:100%; margin-top:10px; background:#3498db; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">
                            ➕ Añadir Producto del Stock
                        </button>
                    </div>

                    <div class="input-group">
                        <label>Precio Cobrado (€)</label>
                        <input type="number" name="precio_total" step="0.01" placeholder="0.00" required style="font-size: 1.2rem; font-weight: bold;">
                    </div>

                    <div class="input-group">
                        <label><strong>¿Lleva factura oficial?</strong></label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="factura" value="1"> SÍ</label>
                            <label class="radio-label"><input type="radio" name="factura" value="0" checked> NO</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-guardar" style="width: 100%; padding: 15px; font-size: 1.1rem; background: #27ae60;">💾 Guardar Trabajo</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <div class="header-tabla-dinamica" style="padding: 10px;">
                    <div class="filtros-fecha" style="display: flex; align-items: flex-end; gap: 10px;">
                        <div class="input-filtro">
                            <label>Desde:</label>
                            <input type="date" id="fechaInicio" class="input-style-mini">
                        </div>
                        <div class="input-filtro">
                            <label>Hasta:</label>
                            <input type="date" id="fechaFin" class="input-style-mini">
                        </div>
                        <button type="button" onclick="filtrarTrabajos()" class="btn-filtro" style="background:#34495e;">🔍</button>
                    </div>
                    
                    <?php if ($esAdmin): ?>
                    <div class="contador-total" style="margin-top: 15px; text-align: right;">
                        <span style="font-size: 0.9rem; color: #7f8c8d;">Total Periodo:</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;" id="totalFacturado">0.00€</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="tabla-scroll-vertical" style="overflow-x: auto;">
                    <table id="tablaTrabajosResponsive">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Fact</th>
                                <th>Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaTrabajos">
                            <?php include '../php/obtener_trabajos.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <template id="templateFilaMaterial">
        <div class="fila-material">
            <select name="productos[]" style="flex:2; padding:8px;">
                <option value="">-- Producto --</option>
                <?php 
                include '../php/conexion.php'; 
                $res = mysqli_query($conexion, "SELECT id, nombre, cantidad FROM productos WHERE cantidad > 0 ORDER BY nombre ASC");
                while($p = mysqli_fetch_assoc($res)) {
                    echo "<option value='{$p['id']}'>{$p['nombre']} ({$p['cantidad']})</option>";
                }
                ?>
            </select>
            <input type="number" name="cantidades[]" value="1" min="1" style="flex:0.5; width:50px; padding:8px;" title="Cantidad">
            <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">X</button>
        </div>
    </template>

    <div id="modalEditarTrabajo" class="modal-overlay">
        <div class="modal-content">
            <h2 style="border-bottom: 2px solid #27ae60; padding-bottom: 10px; margin-top: 0;">Editar Trabajo</h2>
            <form id="formEditarTrabajo">
                <input type="hidden" id="edit_trabajo_id" name="id">
                
                <div class="input-group" style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold;">Cliente / Dirección</label>
                    <input type="text" id="edit_trabajo_cliente" name="cliente" required style="width:100%; padding:8px;">
                </div>

                <div class="input-group" style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold;">Descripción</label>
                    <textarea id="edit_trabajo_desc" name="description" rows="3" style="width:100%; padding:8px;"></textarea>
                </div>

                <div class="input-group" style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold;">Precio Cobrado (€)</label>
                    <input type="number" id="edit_trabajo_precio" name="precio_total" step="0.01" required style="width:100%; padding:8px;">
                </div>

                <div class="input-group" style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold;">Estado</label>
                    <select id="edit_trabajo_estado" name="estado" style="width:100%; padding:8px;">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Cobrado">Cobrado ✅</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="flex:1; background:#27ae60; color:white; padding:12px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Actualizar</button>
                    <button type="button" onclick="cerrarModal('modalEditarTrabajo')" style="flex:1; background:#95a5a6; color:white; padding:12px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/trabajos.js?v=1.3"></script>
    <script>
        // Función rápida para el botón de añadir material
        function agregarFilaMaterial() {
            const temp = document.getElementById('templateFilaMaterial');
            const clone = temp.content.cloneNode(true);
            document.getElementById('contenedor-materiales').appendChild(clone);
        }
    </script>
</body>
</html>