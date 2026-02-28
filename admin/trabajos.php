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

// 1. Obtenemos lista de todos los usuarios para el filtro de suma (Admin)
$usuarios_list_filtro = [];
$res_u = mysqli_query($conexion, "SELECT id, nombre FROM usuarios ORDER BY nombre ASC");
while($u = mysqli_fetch_assoc($res_u)) {
    $usuarios_list_filtro[] = $u;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="manifest" href="../manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#2c3e50">
    <link rel="apple-touch-icon" href="../img/logo_pwa_192.png">

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('../sw.js')
            .then(reg => console.log('PWA lista en Gestión de Trabajos'))
            .catch(err => console.error('Error PWA Trabajos:', err));
        });
      }
    </script>
    <title>Trabajos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="--/css/header.css">
    <link rel="stylesheet" href="../css/main.css?v=4.0">
    <link rel="stylesheet" href="../css/formularios.css?v=4.0">
    <link rel="stylesheet" href="../css/trabajos_layout.css?v=4.0">
    <style>
    /* --- ESTILO DEL HEADER --- */
    header { 
        background-color: #2c3e50 !important; 
        padding: 10px 15px !important;
        display: flex !important;
        position: sticky; /* Crítico para que el menú se posicione debajo */
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-content h1 { 
        margin: 0; 
        font-size: 1.5rem; 
        color: white; 
    }

    .logo-img { 
        height: 40px; 
        width: auto; 
    }

    /* --- ESTILO HAMBURGUESA --- */
    #menu-toggle { display: none; }

.hamburger {
    display: none; /* Oculto en PC */
    color: white;
    font-size: 35px;
    text-align: center;
    cursor: pointer;
    padding: 10px;
}

.nav-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

    /* ESTILO GENERAL DEL MENÚ (PC) */
    .nav-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        width: 100%;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 0.85rem;
        transition: background 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: inline-block;
    }

    .btn-header:hover { background: rgba(255, 255, 255, 0.2); }
    
    /* --- RESPONSIVIDAD (MÓVIL) --- */
    @media (max-width: 1024px) {
    .hamburger { display: block; } /* Mostrar icono en móvil */
    
    .nav-container {
        display: none; /* OCULTAR MENÚ POR DEFECTO EN MÓVIL */
        flex-direction: column;
        gap: 5px;
        position: absolute;
        top: 100%; /* Ajustar según altura de header */
        left: 0;
        width: 100%;
        background: #2c3e50; /* Color de fondo del header */
        padding: 10px 0;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    /* MOSTRAR MENÚ AL HACER CLIC EN EL ICONO */
    #menu-toggle:checked ~ .nav-container {
        display: flex;
    }

    .btn-header { width: 90%; margin: 5px auto; text-align: center; }
}
    
    /* --- RESTO DE ESTILOS --- */
    #resumenDinero {
        margin-top: 15px;
        padding: 15px;
        background: #2c3e50;
        color: white;
        border-radius: 8px;
        text-align: right;
        font-size: 1.2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .trabajos-container-dual { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 20px; 
        padding: 15px; 
        box-sizing: border-box; 
        width: 100%;
    }

    .columna-formulario { 
        flex: 1; 
        min-width: 0; 
        width: 100%;
        max-width: 420px; 
    }
    
    .columna-tabla { 
        flex: 2.5; 
        min-width: 0; 
        width: 100%;
    }
    
    .table-card table { 
        width: 100%; 
        border-collapse: collapse; 
    }
    
    .table-card th, .table-card td { 
        padding: 14px 10px; 
        vertical-align: middle; 
        border-bottom: 1px solid #eee; 
        text-align: left; 
    }

    .table-card tr:hover { background-color: #f8fbff; }
    
    .col-operario div {
        background: #e8f4fd; 
        padding: 5px 10px; 
        border-radius: 20px; 
        display: inline-block; 
        font-weight: 600; 
        color: #2980b9; 
        font-size: 0.8rem; 
        border: 1px solid #d1e9f9;
    }

    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .modal-content { background-color: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; }

    /* --- ESTILO EXCLUSIVO PARA EL TÍTULO EN PDF --- */
    .titulo-impresion {
        display: none;
        text-align: center;
        margin-bottom: 20px;
    }

    @media (max-width: 1024px) {
        .columna-formulario, .columna-tabla {
            min-width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
        }
        .tabla-scroll-vertical {
            overflow-x: auto !important;
            width: 100% !important;
            -webkit-overflow-scrolling: touch;
        }
        table { min-width: 600px; }
    }

    /* --- CONFIGURACIÓN PARA EL PDF / IMPRESIÓN --- */
    @media print {
        .titulo-impresion {
            display: block !important;
        }
        .titulo-impresion h1 {
            margin: 0;
            font-size: 26pt;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .titulo-impresion p {
            margin: 5px 0;
            font-size: 12pt;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        header, .hamburger, .nav-container, .columna-formulario, .buscador-localidad-container, 
        #info-filtro, .header-tabla-dinamica, .col-accion, .btn-reset, 
        .btn-print, .btn-lupa, button, .modal {
            display: none !important;
        }

        body { background: white !important; margin: 0; padding: 0; }
        .trabajos-container-dual { display: block !important; padding: 0 !important; }
        .columna-tabla { width: 100% !important; max-width: 100% !important; flex: none !important; }
        .table-card { box-shadow: none !important; border: none !important; }
        table { width: 100% !important; border-collapse: collapse !important; font-size: 10pt !important; }
        th { background-color: #f1f4f6 !important; color: black !important; border: 1px solid #ddd !important; -webkit-print-color-adjust: exact; }
        td { border: 1px solid #ddd !important; padding: 8px !important; }

        #resumenDinero {
            background: #f1f4f6 !important;
            color: black !important;
            border: 2px solid #2c3e50 !important;
            margin-top: 20px !important;
            font-size: 14pt !important;
            -webkit-print-color-adjust: exact;
        }
        tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>

    <div class="titulo-impresion">
    <h1>CERRAJERÍA PINOS</h1>
    <p>Reporte de Trabajos Realizados - Fecha de impresión: <?php echo date('d/m/Y'); ?></p>
    
    <div id="filtros-reporte-print" style="margin-top: 10px; font-size: 14pt; font-weight: bold; color: #34495e;">
        <span id="txtFiltroEmpleado"></span> 
        <span id="txtFiltroFechas"></span>
    </div>
</div>

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
            <a href="dashboard.php">
                <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Trabajos</h1>
        </div>

        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="hamburger">&#9776;</label>

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
                <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
                <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
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
    <label>Empresa / Cliente Particular <strong>*</strong></label>
    <input type="text" name="nombre_cliente" id="nombre_cliente" list="listaEmpresas" class="input-style" placeholder="Seleccione empresa o escriba nombre del cliente..." required>
    
    <datalist id="listaEmpresas">
        <?php
        // Traemos las empresas de siempre para que sigan apareciendo como sugerencias
        $res_emp = mysqli_query($conexion, "SELECT nombre FROM empresas ORDER BY nombre ASC");
        while($e = mysqli_fetch_assoc($res_emp)) {
            echo "<option value='".htmlspecialchars($e['nombre'])."'>";
        }
        ?>
    </datalist>
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
                    <input type="text" id="busquedaLocalidad" class="input-busqueda" placeholder="🔍 Buscar por localidad..." onkeyup="filtrarTrabajos()">
                    <div id="info-filtro">📍 <span id="resumenFiltro">Resultados actuales</span></div>
                </div>

                <div class="header-tabla-dinamica" style="padding: 10px; background: #f8f9fa; border-bottom: 1px solid #eee;">
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <input type="date" id="fechaInicio" class="input-style-mini" style="flex:1; min-width: 120px;">
                        <input type="date" id="fechaFin" class="input-style-mini" style="flex:1; min-width: 120px;">
                        
                        <?php if ($esAdmin): ?>
                            <select id="filtroEmpleado" class="input-style-mini" style="flex:1; min-width: 150px; padding: 5px;">
                                <option value="todos">-- Todos los Empleados --</option>
                                <?php foreach($usuarios_list_filtro as $u): ?>
                                    <option value="<?php echo htmlspecialchars($u['nombre']); ?>">
                                        <?php echo htmlspecialchars($u['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="hidden" id="filtroEmpleado" value="todos">
                        <?php endif; ?>

                        <button type="button" onclick="filtrarTrabajos()" class="btn-lupa" style="padding: 5px 15px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">Filtrar y Sumar</button>
                        <button type="button" onclick="limpiarTodosLosFiltros()" class="btn-reset" style="padding: 5px 10px; background: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer;">Limpiar</button>
                        <button type="button" onclick="window.print()" class="btn-print" style="padding: 5px 15px; background: #e67e22; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">📄 Imprimir</button>
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
                
                <div id="resumenDinero">
                    <strong>Suma Total: </strong> <span id="sumaTotal">0.00</span>€
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
        function filtrarTrabajos() {
    const fInicio = document.getElementById('fechaInicio').value; 
    const fFin = document.getElementById('fechaFin').value;       
    const busquedaLoc = document.getElementById('busquedaLocalidad').value.toLowerCase().trim();
    const filtroEmp = document.getElementById('filtroEmpleado').value.toLowerCase().trim();
    
    const filas = document.querySelectorAll('#cuerpoTablaTrabajos tr');
    let sumaCaja = 0;

    filas.forEach(fila => {
        // LEER LA FECHA DESDE EL ATRIBUTO DATA-FEC
        const celdaFecha = fila.cells[0];
        const fechaTextoFila = celdaFecha.getAttribute('data-fec') || ""; 
        
        let fechaConvertida = "";
        const partes = fechaTextoFila.split('/');
        if (partes.length === 3) {
            // Convertimos DD/MM/YYYY a YYYY-MM-DD
            fechaConvertida = `${partes[2]}-${partes[1]}-${partes[0]}`;
        }

        const infoFila = fila.cells[1].innerText.toLowerCase();
        let empleadoFila = "todos";
        let precioTexto = "0";

        // Ajuste de columnas según rol
        if (<?php echo $esAdmin ? 'true' : 'false'; ?>) {
            empleadoFila = fila.cells[2] ? fila.cells[2].innerText.toLowerCase().trim() : "";
            precioTexto = fila.cells[4] ? fila.cells[4].innerText : "0";
        } else {
            precioTexto = fila.cells[3] ? fila.cells[3].innerText : "0";
        }

        let mostrar = true;

        // Lógica de filtros
        if (fInicio && fechaConvertida < fInicio) mostrar = false;
        if (fFin && fechaConvertida > fFin) mostrar = false;
        if (busquedaLoc && !infoFila.includes(busquedaLoc)) mostrar = false;
        
        // El filtro de empleado debe buscar el nombre dentro del texto del badge
        if (filtroEmp !== 'todos' && !empleadoFila.includes(filtroEmp)) mostrar = false;

        if (mostrar) {
            fila.style.display = '';
            // Limpiar precio para sumar: quita €, puntos de mil y cambia coma por punto
            const precioLimpio = precioTexto.replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.');
            sumaCaja += parseFloat(precioLimpio) || 0;
        } else {
            fila.style.display = 'none';
        }
    });
// ... (dentro de filtrarTrabajos, al final)

// Actualizar textos para la impresión
const txtEmp = document.getElementById('txtFiltroEmpleado');
const txtFec = document.getElementById('txtFiltroFechas');
const selectorEmp = document.getElementById('filtroEmpleado');

// Texto del empleado
if (selectorEmp && selectorEmp.value !== 'todos') {
    txtEmp.innerText = "Empleado: " + selectorEmp.options[selectorEmp.selectedIndex].text + " | ";
} else {
    txtEmp.innerText = "Todos los empleados | ";
}

// Texto de fechas
if (fInicio || fFin) {
    const dInicio = fInicio ? fInicio.split('-').reverse().join('/') : '...';
    const dFin = fFin ? fFin.split('-').reverse().join('/') : '...';
    txtFec.innerText = "Periodo: " + dInicio + " al " + dFin;
} else {
    txtFec.innerText = "Periodo: Histórico completo";
}
    // Actualizar el total visual
    document.getElementById('sumaTotal').innerText = sumaCaja.toLocaleString('es-ES', { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
    });
}

        function limpiarTodosLosFiltros() {
            document.getElementById('fechaInicio').value = '';
            document.getElementById('fechaFin').value = '';
            document.getElementById('busquedaLocalidad').value = '';
            if (document.getElementById('filtroEmpleado')) document.getElementById('filtroEmpleado').value = 'todos';
            filtrarTrabajos();
        }

        window.onload = filtrarTrabajos;

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