<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1.
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
include '../php/conexion.php';
include '../php/conexion.php';
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../index.html"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <style>
        .container-empresas { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .grid-gestion { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-ver-trabajos { background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; transition: background 0.3s; }
        .btn-ver-trabajos:hover { background: #2980b9; }
        .btn-eliminar { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
        .btn-eliminar:hover { background: #c0392b; }
        .alerta-exito { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
    
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
            <h1>Empresas</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="gastos.php" class="btn-header">💸 Gastos </a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="container-empresas">
        
        <?php if (isset($_GET['msj'])): ?>
            <div class="alerta-exito">
                ✅ <?php echo htmlspecialchars($_GET['msj']); ?>
            </div>
        <?php endif; ?>

        <div class="grid-gestion">
            <section class="card">
                <h3>Nueva Empresa</h3>
                <form action="../php/guardar_empresa.php" method="POST" class="mi-formulario">
                    <div class="input-group">
                        <label>Nombre de Empresa</label>
                        <input type="text" name="nombre" placeholder="Ej: MAPFRE" required>
                    </div>
                    <div class="input-group">
                        <label>CIF / NIF</label>
                        <input type="text" name="cif" placeholder="A12345678">
                    </div>
                    <div class="input-group">
                        <label>Teléfono Directo</label>
                        <input type="text" name="telefono" placeholder="968...">
                    </div>
                    <button type="submit" class="btn-guardar" style="width: 100%; margin-top: 15px;">💾 Guardar Empresa</button>
                </form>
            </section>

            <section class="card">
                <h3>Empresas Colaboradoras</h3>
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding:12px; text-align:left;">Empresa</th>
                            <th style="padding:12px; text-align:left;">Contacto</th>
                            <th style="padding:12px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM empresas ORDER BY nombre ASC";
                        $res = mysqli_query($conexion, $query);
                        
                        if(mysqli_num_rows($res) > 0){
                            while($emp = mysqli_fetch_assoc($res)):
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding:12px;">
                                <strong><?php echo htmlspecialchars($emp['nombre']); ?></strong><br>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($emp['cif']); ?></small>
                            </td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($emp['telefono'] ?: '---'); ?></td>
                            <td style="padding:12px; text-align:center;">
                                <div style="display:flex; gap:8px; justify-content:center;">
                                    <a href="../php/trabajo_empresas.php?nombre=<?php echo urlencode($emp['nombre']); ?>" class="btn-ver-trabajos">📂 Ver Trabajos</a>
                                    
                                    <button onclick="eliminarEmpresa(<?php echo $emp['id']; ?>)" class="btn-eliminar">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        } else {
                            echo "<tr><td colspan='3' style='text-align:center; padding:30px; color:#95a5a6;'>No hay empresas registradas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <script>
        function eliminarEmpresa(id) {
            if(confirm('¿Estás seguro? Se borrará la empresa de la lista, pero los trabajos realizados NO se borrarán.')) {
                // RUTA CORREGIDA: sale de vistas/ y entra en php/
                window.location.href = '../php/eliminar_empresa.php?id=' + id;
            }
        }
        // Si acabamos de guardar, bajamos automáticamente al final de la tabla
if (window.location.search.includes('msj=')) {
    window.scrollTo(0, document.body.scrollHeight);
}
    </script>
</body>
</html>