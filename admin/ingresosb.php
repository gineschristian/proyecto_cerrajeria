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
    <title>Extras (Caja B) - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
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
            <img src="../img/logo.png" alt="Logo" class="logo-img">
            <h1>Control de Extras - Cerrajeria Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header" > 🏠 Panel de Control</a>
        <a href="stock.php" class="btn-header" > 📦 Stock</a>
        <a href="impuestos.php" class="btn-header" >📊 Impuestos</a>
        <a href="gestion_usuarios.php" class="btn-header">👥 Empleados </a>
        <a href="ingresos.php" class="btn-header" >💰 Ingresos</a>
        <a href="trabajos.php" class="btn-header" >🛠️ Trabajos</a>
        <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
        <a href="gastos.php" class="btn-header" > 💸 Gastos</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #e67e22;">
                <h2>Añadir Ingreso Extra</h2>
                <p style="font-size: 0.8em; color: #666; margin-bottom: 15px;">Use esto para ventas directas o ingresos que no sean trabajos de cerrajería.</p>
                
                <form id="formExtraManual" class="mi-formulario">
                    <div class="input-group">
                        <label>Concepto</label>
                        <input type="text" name="concepto" placeholder="Ej: Venta mando garaje" required>
                    </div>

                    <div class="input-group">
                        <label>Monto (€)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required>
                    </div>

                    <button type="submit" class="btn-guardar" style="background: #e67e22;">Guardar en Caja B</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <div class="header-tabla-dinamica">
                    <h2>Listado de Dinero Extra</h2>
                    <div class="contador-total" style="background: #e67e22;">
                        <span class="etiqueta">Acumulado B:</span>
                        <span class="cifra" id="totalExtras" style="color: black;">0.00€</span>
                    </div>
                </div>

                <div class="tabla-scroll-vertical">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Origen</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaExtras">
                            <?php include '../php/obtener_extras.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
<div id="modalEditarExtra" class="modal-overlay" style="display:none;">
    <div class="card-formulario modal-content">
        <h2 style="color: #e67e22;">Editar Ingreso B</h2>
        <form id="formEditarExtra" class="mi-formulario">
            <input type="hidden" name="id" id="edit_extra_id">
            <div class="input-group">
                <label>Concepto</label>
                <input type="text" name="concepto" id="edit_extra_concepto" required>
            </div>
            <div class="input-group">
                <label>Monto (€)</label>
                <input type="number" name="monto" id="edit_extra_monto" step="0.01" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-guardar" style="background: #e67e22; flex: 1;">Actualizar</button>
                <button type="button" onclick="cerrarModal('modalEditarExtra')" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>
    <script src="../js/extras.js"></script>
</body>
</html>