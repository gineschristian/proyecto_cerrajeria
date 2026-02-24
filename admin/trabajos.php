<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');
$id_usuario_actual = $_SESSION['usuario_id'];
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
        .trabajos-container-dual { display: flex; flex-wrap: wrap; gap: 20px; padding: 15px; box-sizing: border-box; }
        .columna-formulario { flex: 1; min-width: 380px; max-width: 420px; }
        .columna-tabla { flex: 2.5; min-width: 600px; }
        
        /* --- DISEÑO DE TABLA MEJORADO --- */
        .table-card table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: auto; /* Permite que las columnas respiren según contenido */
        }
        
        .table-card th, .table-card td { 
            padding: 14px 10px; 
            vertical-align: middle; 
            border-bottom: 1px solid #eee; 
            text-align: left; 
        }

        /* Colores y efectos */
        .table-card tr:hover { background-color: #f8fbff; }
        
        /* Anchos específicos para evitar solapamiento */
        .col-fecha { width: 75px; }
        .col-info { min-width: 250px; }
        <?php if ($esAdmin): ?>
        .col-operario { width: 130px; text-align: center; }
        <?php endif; ?>
        .col-factura { width: 85px; text-align: center; }
        .col-total { width: 110px; text-align: right; white-space: nowrap; }
        .col-accion { width: 120px; text-align: center; }

        .zona-material { 
            background: #f1f4f6; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px dashed #3498db; box-sizing: border-box; width: 100%;
        }
        
        .fila-material { 
            background: #fff; padding: 10px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #ddd; display: flex; flex-direction: column; gap: 8px; box-sizing: border-box; width: 100%;
        }

        .fila-material-controles { display: flex; gap: 8px; align-items: center; width: 100%; box-sizing: border-box; }
        .input-buscador-material { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box; }

        .buscador-localidad-container { background: #fff; padding: 15px; border-radius: 8px 8px 0 0; border-bottom: 2px solid #3498db; }
        .input-busqueda { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        
        .btn-accion { padding: 8px; border: none; border-radius: 6px; cursor: pointer; color: white; transition: 0.2s; }
        .btn-accion:hover { transform: scale(1.1); }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background-color: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
    </style>
</head>
<body>

    <datalist id="listaProductos">
        <?php 
        $id_user = $_SESSION['usuario_id'];
        $sql_p = "SELECT p.id, p.nombre, p.cant_almacen, COALESCE(su.cantidad, 0) as cant_furgo
                  FROM productos p
                  LEFT JOIN stock_usuarios su ON p.id = su.id_producto AND su.id_usuario = $id_user
                  ORDER BY p.nombre ASC";
        $res_p = mysqli_query($conexion, $sql_p);
        while($p = mysqli_fetch_assoc($res_p)) {
            echo "<option data-id='{$p['id']}' value='" . htmlspecialchars($p['nombre']) . " (F: {$p['cant_furgo']} | T: {$p['cant_almacen']})'>";
        }
        ?>
    </datalist>

    <template id="templateFilaMaterial">
        <div class="fila-material">
            <div style="width: 100%;">
                <input list="listaProductos" name="productos_nombres[]" class="input-buscador-material" placeholder="🔍 Buscar material..." onchange="actualizarIdProducto(this)" required>
                <input type="hidden" name="productos[]">
            </div>
            <div class="fila-material-controles">
                <select name="origenes[]" style="flex: 1; padding: 8px; border-radius: 5px; border: 1px solid #ccc;">
                    <option value="furgoneta">🚐 Furgo</option>
                    <option value="taller">🏭 Taller</option>
                </select>
                <input type="number" name="cantidades[]" value="1" min="1" style="width: 55px; padding: 8px; text-align: center; border-radius: 5px; border: 1px solid #ccc;">
                <button type="button" class="btn-quitar" onclick="this.closest('.fila-material').remove()" style="background:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer;">X</button>
            </div>
        </div>
    </template>

    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Trabajos - Cerrajería Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
                <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <?php endif; ?>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        <aside class="columna-formulario">
            <div class="card-formulario">
                <h2>Nuevo Trabajo</h2>
                <form id="formTrabajo" class="mi-formulario">
                    <div class="grid-campos">
                        <div class="input-group"><label>Teléfono <strong>*</strong></label><input type="tel" name="telefono" placeholder="600000000" required></div>
                        <div class="input-group"><label>Localidad <strong>*</strong></label><input type="text" name="localidad" placeholder="Ej: Lorca" required></div>
                    </div>
                    <div class="input-group">
                    <label>Empresa / Cliente</label>
                        <select name="nombre_cliente" id="nombre_cliente" class="input-style">
                            <option value="">-- Cliente Particular --</option>
                            <?php
        // Traemos las empresas que el cliente ha creado en su módulo
        $res_emp = mysqli_query($conexion, "SELECT nombre FROM empresas ORDER BY nombre ASC");
        while($e = mysqli_fetch_assoc($res_emp)) {
            echo "<option value='".htmlspecialchars($e['nombre'])."'>".htmlspecialchars($e['nombre'])."</option>";
        }
        ?>
    </select>
    <small>Si es un particular, déjalo en blanco y usa el campo 'Dirección'.</small>
</div>
                    <div class="input-group"><label>Dirección <strong>*</strong></label><input type="text" name="cliente" placeholder="Calle, número, piso..." required></div>
                    <div class="input-group"><label>Descripción</label><textarea name="description" placeholder="¿Qué se ha hecho?" rows="2"></textarea></div>
                    <div class="zona-material">
                        <label><strong>Materiales utilizados</strong></label>
                        <div id="contenedor-materiales" style="margin-top: 10px;"></div>
                        <button type="button" onclick="agregarFilaMaterial()" style="width:100%; margin-top:10px; background:#3498db; color:white; border:none; padding:12px; border-radius:5px; cursor:pointer; font-weight:bold;">➕ Añadir Material</button>
                    </div>
                    <div class="grid-campos">
                        <div class="input-group"><label>Precio Cobrado (€)</label><input type="number" name="precio_total" step="0.01" required style="font-size: 1.3rem; font-weight: bold; color: #27ae60;"></div>
                        <div class="input-group">
                            <label>Factura</label>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="factura" value="1"> SÍ</label>
                                <label class="radio-label"><input type="radio" name="factura" value="0" checked> NO</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-guardar" style="width: 100%; padding: 15px; background: #27ae60; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; margin-top:10px;">💾 Guardar Trabajo</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <div class="buscador-localidad-container">
                    <input type="text" id="busquedaLocalidad" class="input-busqueda" placeholder="🔍 Buscar por localidad..." onkeyup="filtrarLocalidad()">
                    <div id="info-filtro">📍 <span id="resumenFiltro">...</span></div>
                </div>

                <div class="header-tabla-dinamica" style="padding: 10px; background: #f8f9fa; border-bottom: 1px solid #eee;">
                    <div style="display: flex; gap: 8px;">
                        <input type="date" id="fechaInicio" class="input-style-mini" style="flex:1;">
                        <input type="date" id="fechaFin" class="input-style-mini" style="flex:1;">
                        <button type="button" onclick="filtrarTrabajos()" class="btn-lupa" style="padding: 5px 15px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">Filtrar</button>
                    </div>
                </div>

                <div class="tabla-scroll-vertical">
                    <table id="tablaTrabajos">
                        <thead>
                            <tr style="background: #f1f4f6;">
                                <th class="col-fecha">Fecha</th>
                                <th class="col-info">Información Cliente</th>
                                <?php if ($esAdmin): ?>
                                <th class="col-operario">Operario</th>
                                <?php endif; ?>
                                <th class="col-factura">Factura</th>
                                <th class="col-total">Total</th>
                                <th class="col-accion">Acción</th>
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

    <div id="modalEditarTrabajo" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom:15px;">Editar Trabajo</h2>
            <form id="formEditarTrabajo" class="mi-formulario">
                <input type="hidden" name="id" id="edit_trabajo_id">
                <div class="grid-campos">
                    <div class="input-group"><label>Teléfono</label><input type="tel" name="telefono" id="edit_trabajo_telefono" required></div>
                    <div class="input-group"><label>Localidad</label><input type="text" name="localidad" id="edit_trabajo_localidad" required></div>
                </div>
                <div class="input-group"><label>Nombre Cliente</label><input type="text" name="nombre_cliente" id="edit_trabajo_nombre_cliente"></div>
                <div class="input-group"><label>Dirección</label><input type="text" name="cliente" id="edit_trabajo_cliente" required></div>
                <div class="input-group"><label>Descripción</label><textarea name="description" id="edit_trabajo_description" rows="2"></textarea></div>
                <div class="input-group"><label>Precio (€)</label><input type="number" name="precio_total" id="edit_trabajo_precio" step="0.01" required></div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-guardar" style="flex:1; background:#27ae60; color:white; border:none; padding:12px; border-radius:5px; cursor:pointer;">Guardar</button>
                    <button type="button" onclick="cerrarModal('modalEditarTrabajo')" style="flex:1; background:#95a5a6; color:white; border:none; padding:12px; border-radius:5px; cursor:pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/trabajos.js?v=<?php echo time(); ?>"></script>
    <script>
        function actualizarIdProducto(input) {
            const list = document.getElementById('listaProductos');
            const option = Array.from(list.options).find(opt => opt.value === input.value);
            const hiddenInput = input.parentElement.querySelector('input[name="productos[]"]');
            if (option) { hiddenInput.value = option.getAttribute('data-id'); } 
            else { hiddenInput.value = ""; }
        }

        window.agregarFilaMaterial = function() {
            const contenedor = document.getElementById('contenedor-materiales');
            const temp = document.getElementById('templateFilaMaterial');
            if (contenedor && temp) {
                const clone = temp.content.cloneNode(true);
                contenedor.appendChild(clone);
            }
        };

        function cerrarModal(id) { document.getElementById(id).style.display = 'none'; }
    </script>
</body>
</html>