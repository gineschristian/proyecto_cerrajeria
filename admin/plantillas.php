<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.html");
    exit();
}
$esAdmin = ($_SESSION['rol'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Plantillas - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Plantillas y Documentos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="gestion_usuarios.php" class="btn-header">👥 Empleados </a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <?php endif; ?>

            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="container">
        <section class="card-formulario">
            <h2>Documentación de Trabajo</h2>
            <p>Aquí puedes descargar las plantillas necesarias para el día a día.</p>
            
            <div class="lista-plantillas" style="margin-top: 20px; display: grid; gap: 15px;">
                <div class="item-plantilla" style="padding: 15px; border: 1px solid #ddd; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span>Hoja de Servicio Estándar (PDF)</span>
                    <a href="../docs/hoja_servicio.pdf" class="btn-header" style="background: #27ae60;" download>Descargar</a>
                </div>

                <?php if ($esAdmin): ?>
                <div class="item-plantilla" style="padding: 15px; border: 1px solid #e74c3c; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span>Modelo de Contrato Empleado (DOCX)</span>
                    <a href="../docs/modelo_contrato.docx" class="btn-header" style="background: #c0392b;" download>Descargar</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>