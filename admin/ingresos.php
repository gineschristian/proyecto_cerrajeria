<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si existe la sesión
if (!isset($_SESSION['usuario_id'])) {
    // CORRECCIÓN: Apuntamos al index.html en la raíz
    header("Location: ../index.html");
    exit();
}

// 2. Verificamos el rol (usando trim por seguridad de la BD)
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol !== 'admin') {
    // Si es empleado, lo devolvemos al dashboard con un mensaje
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}

// Si llega aquí, es Admin y puede ver el contenido
include '../php/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Ingresos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css"> 
    <style>
        /* Ajuste específico para las tarjetas de resumen en móvil */
        @media (max-width: 768px) {
            .tarjetas-resumen {
                flex-direction: column !important;
            }
            .header-tabla-dinamica {
                flex-direction: column;
                gap: 15px;
            }
            .filtros-fecha {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            .filtros-fecha input {
                flex: 1;
            }
        }
        .nav-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 15px;
            }
            .btn-header {
                font-size: 0.8rem;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Ingresos - Cerrajeria Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="gastos.php" class="btn-header">💸 Gastos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="trabajos-layout" style="padding: 10px;"> 
        <div class="tarjetas-resumen" style="width: 100%; display: flex; gap: 20px;">
            <div class="card oficial">
                <h3>Facturación (A)</h3>
                <p id="totalOficial">0.00€</p>
                <small>Legal / Con IVA</small>
            </div>
            <div class="card extra">
                <h3>Extras (B)</h3>
                <p id="totalB">0.00€</p>
                <small>Caja B / Sin Factura</small>
            </div>
            <div class="card total-caja">
                <h3>Caja Total</h3>
                <p id="totalGeneral">0.00€</p>
                <small>Efectivo + Banco</small>
            </div>
        </div>

        <section class="table-card" style="width: 100%; margin-top: 20px; padding: 15px;">
            <div class="header-tabla-dinamica" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="border:none; margin:0;">Libro de Ingresos</h2>
                <div class="filtros-fecha">
                    <input type="date" id="fechaInicio" class="input-style-mini">
                    <input type="date" id="fechaFin" class="input-style-mini">
                    <button onclick="filtrarIngresos()" class="btn-filtro" style="padding: 8px 15px; background: var(--rojo-principal); color: white; border: none; border-radius: 5px; cursor: pointer;">Filtrar</button>
                </div>
            </div>

            <div class="tabla-scroll-vertical">
                <table class="user-table"> <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaIngresos">
                        <?php include '../php/obtener_ingresos_totales.php'; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="modalEditarIngreso" class="modal-overlay" style="display:none;">
        <div class="card-formulario modal-content">
            <h2 style="color: #27ae60; margin-bottom: 20px;">EDITAR INGRESO</h2>
            <form id="formEditarIngreso" class="mi-formulario">
                <input type="hidden" name="id" id="edit_ingreso_id">
                <input type="hidden" name="tabla" id="edit_ingreso_tabla">
                
                <div class="input-group">
                    <label>Concepto / Cliente</label>
                    <input type="text" name="concepto" id="edit_ingreso_concepto" required>
                </div>
                
                <div class="input-group">
                    <label>Monto (€)</label>
                    <input type="number" name="monto" id="edit_ingreso_monto" step="0.01" required>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #27ae60; flex: 1;">Actualizar</button>
                    <button type="button" onclick="cerrarModal('modalEditarIngreso')" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/ingresos.js"></script>
</body>
</html>