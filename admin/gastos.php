<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.html");
    exit();
}
if ($_SESSION['rol'] !== 'admin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Gastos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
        /* Ajustes específicos para el layout dual en móvil */
        @media (max-width: 992px) {
            .trabajos-container-dual {
                display: flex;
                flex-direction: column;
                gap: 20px;
                padding: 10px;
            }
            .columna-formulario, .columna-tabla {
                width: 100% !important;
            }
            .header-tabla-dinamica {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo" class="logo-img">
            <h1>Control de Gastos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #c0392b;">
                <h2>Registrar Compra / Gasto</h2>
                <form id="formGasto" class="mi-formulario">
                    <div class="input-group">
                        <label>Concepto / Proveedor</label>
                        <input type="text" name="concepto" placeholder="Ej: Compra bombines Yale" required>
                    </div>

                    <div class="input-group">
                        <label>Categoría</label>
                        <select name="categoria" class="input-style">
                            <option value="Material">Material / Stock</option>
                            <option value="Herramientas">Herramientas</option>
                            <option value="Gasolina">Gasolina / Vehículo</option>
                            <option value="Local">Alquiler / Suministros</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Monto Total (€)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required>
                    </div>

                    <div class="input-group">
                        <label for="gasto_factura">¿Tiene factura oficial?</label>
                        <select name="factura" id="gasto_factura" class="input-style" required>
                            <option value="0">No (Ticket/Recibo B)</option>
                            <option value="1">Sí (Factura con IVA)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-guardar" style="background: #c0392b; width: 100%; margin-top: 10px;">Registrar Gasto</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <div class="header-tabla-dinamica">
                    <h2 style="margin:0; border:none;">Historial de Gastos</h2>
                    <div class="contador-total" style="background: #f5b7b1; padding: 10px 20px; border-radius: 10px;">
                        <span class="etiqueta" style="font-weight:bold;">Total Gastado:</span>
                        <span class="cifra" id="totalGastos" style="color: black; font-weight:bold; font-size: 1.2rem;">0.00€</span>
                    </div>
                </div>

                <div class="tabla-scroll-vertical">
                    <table class="user-table"> <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaGastos">
                            <?php include '../php/obtener_gastos.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="modalEditarGasto" class="modal-overlay" style="display:none;">
        <div class="card-formulario modal-content">
            <h2 style="color: #c0392b; margin-bottom: 20px;">Editar Gasto</h2>
            <form id="formEditarGasto" class="mi-formulario">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="input-group">
                    <label>Concepto / Proveedor</label>
                    <input type="text" name="concepto" id="edit_concepto" required>
                </div>

                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" id="edit_categoria" class="input-style">
                        <option value="Material">Material / Stock</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Gasolina">Gasolina / Vehículo</option>
                        <option value="Local">Alquiler / Suministros</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Monto (€)</label>
                    <input type="number" name="monto" id="edit_monto" step="0.01" required>
                </div>

                <div class="input-group">
                    <label>Factura</label>
                    <select name="con_factura" id="edit_factura" class="input-style">
                        <option value="1">Sí (Oficial)</option>
                        <option value="0">No (Ticket)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #c0392b; flex: 1;">Guardar</button>
                    <button type="button" onclick="cerrarModal()" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cerrar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/gastos.js"></script>
</body>
</html>