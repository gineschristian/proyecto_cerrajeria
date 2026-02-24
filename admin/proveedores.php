<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Seguridad: Solo Admin
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    header("Location: ../index.html");
    exit();
}

include '../php/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo" class="logo-img">
            <h1>Proveedores</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gastos.php" class="btn-header">💸 Gastos </a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual">
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #f39c12;">
                <h2>Nuevo Proveedor</h2>
                <form action="../php/guardar_proveedor.php" method="POST" class="mi-formulario">
                    <div class="input-group">
                        <label>Nombre del Proveedor / Almacén</label>
                        <input type="text" name="nombre" placeholder="Ej: Leroy Merlin" required>
                    </div>
                    <div class="input-group">
                        <label>CIF / NIF (Opcional)</label>
                        <input type="text" name="cif" placeholder="B12345678">
                    </div>
                    <div class="input-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" name="telefono" placeholder="600000000">
                    </div>
                    <button type="submit" class="btn-guardar" style="background: #f39c12; width: 100%;">Añadir Proveedor</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <h2>Mis Proveedores</h2>
                <div class="tabla-scroll-vertical">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>CIF</th>
                                <th>Gastado Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Consulta que une Proveedores con la suma de sus Gastos
                            $sql = "SELECT p.*, 
                                   (SELECT SUM(monto) FROM gastos WHERE proveedor = p.nombre) as total_gastado 
                                   FROM proveedores p 
                                   ORDER BY p.nombre ASC";
                            
                            $resultado = mysqli_query($conexion, $sql);

                            while ($row = mysqli_fetch_assoc($resultado)) {
                                $total_formateado = number_format($row['total_gastado'] ?? 0, 2);
                                echo "<tr>
                                    <td style='font-weight:bold;'>".htmlspecialchars($row['nombre'])."</td>
                                    <td>".htmlspecialchars($row['cif'])."</td>
                                    <td style='color:#c0392b; font-weight:bold;'>- {$total_formateado}€</td>
                                    <td>
                                        <div style='display:flex; gap:10px;'>
                                            <a href='../php/gasto_proveedores.php?nombre=".urlencode($row['nombre'])."' class='btn-header' style='background:#3498db; font-size:0.7rem;'>👁️ Ver Historial</a>
                                            <button onclick='eliminarProveedor({$row['id']})' style='background:none; border:none; cursor:pointer;'>🗑️</button>
                                        </div>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
    function eliminarProveedor(id) {
        if (confirm('¿Estás seguro? Se perderá el vínculo con los gastos registrados a este nombre.')) {
            window.location.href = '../php/eliminar_proveedor.php?id=' + id;
        }
    }
    </script>
</body>
</html>