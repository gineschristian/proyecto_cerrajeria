<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. DEFINIMOS LA VARIABLE QUE FALTA
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// 3. Incluimos la conexión con la ruta correcta
include '../php/conexion.php'; 
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
            .then(reg => console.log('PWA lista en Plantillas'))
            .catch(err => console.error('Error PWA Plantillas:', err));
        });
      }
    </script>
    <title>Plantillas - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <style>
        header { 
            background-color: #2c3e50 !important; 
            padding: 10px 15px !important;
            display: block !important;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
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
            font-weight: 500;
            transition: background 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-header:hover {
            background: rgba(255, 255, 255, 0.2);
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
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Plantillas</h1>
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
                <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
                <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
                <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
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